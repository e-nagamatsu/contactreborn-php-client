<?php

namespace ContactReborn;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ContactReborn\Exceptions\ApiException;
use ContactReborn\Exceptions\AuthenticationException;
use ContactReborn\Exceptions\RateLimitException;

/**
 * Contact/Reborn API Client
 * 
 * @package ContactReborn
 */
class ContactRebornClient
{
    /**
     * API Base URL
     */
    const API_BASE_URL = 'https://contact-reborn.net/api';
    
    /**
     * @var string API Token
     */
    private $apiToken;
    
    /**
     * @var Client HTTP Client
     */
    private $httpClient;
    
    /**
     * @var array Default headers
     */
    private $headers = [];
    
    /**
     * @var int Timeout in seconds
     */
    private $timeout = 30;
    
    /**
     * Constructor
     * 
     * @param string $apiToken Your API token
     * @param array $options Optional configuration
     */
    public function __construct(string $apiToken, array $options = [])
    {
        $this->apiToken = $apiToken;
        
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        
        if (isset($options['timeout'])) {
            $this->timeout = $options['timeout'];
        }
        
        $baseUrl = $options['base_url'] ?? self::API_BASE_URL;
        
        $this->httpClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ]);
    }
    
    /**
     * Check if an email is blocked
     * 
     * @param string $email Email address to check
     * @return array Response data
     * @throws ApiException
     */
    public function checkEmail(string $email): array
    {
        try {
            $response = $this->httpClient->post('/check', [
                'json' => [
                    'email' => $email
                ]
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            
            // Determine result based on API response
            $result = \ContactReborn\Enums\CheckResult::PASS;
            if (isset($data['result'])) {
                $result = $data['result'];
            } elseif (isset($data['is_blocked']) && $data['is_blocked']) {
                $result = \ContactReborn\Enums\CheckResult::BLOCK;
            }
            
            return [
                'result' => $result,
                'is_spam' => $data['is_spam'] ?? false, // Deprecated: Use 'is_blocked' instead
                'is_blocked' => $data['is_blocked'] ?? false,
                'reason' => $data['reason'] ?? null,
                'matched_rule' => $data['matched_rule'] ?? null,
                'confidence' => $data['confidence'] ?? null,
                'checked_at' => $data['checked_at'] ?? date('Y-m-d H:i:s'),
            ];
            
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Get user's blocked email list
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     * @throws ApiException
     */
    public function getBlockedEmails(int $page = 1, int $perPage = 20): array
    {
        try {
            $response = $this->httpClient->get('/user-blocked-emails', [
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage
                ]
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
            
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Add email to user's blocked list
     * 
     * @param string $email Email to block
     * @param string|null $reason Optional reason
     * @return array
     * @throws ApiException
     */
    public function addBlockedEmail(string $email, ?string $reason = null): array
    {
        try {
            $data = ['email' => $email];
            if ($reason !== null) {
                $data['reason'] = $reason;
            }
            
            $response = $this->httpClient->post('/user-blocked-emails', [
                'json' => $data
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
            
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Remove email from user's blocked list
     * 
     * @param int $id Blocked email ID
     * @return bool
     * @throws ApiException
     */
    public function removeBlockedEmail(int $id): bool
    {
        try {
            $response = $this->httpClient->delete('/user-blocked-emails/' . $id);
            
            return $response->getStatusCode() === 204 || $response->getStatusCode() === 200;
            
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Batch check multiple emails
     * 
     * @param array $emails Array of email addresses
     * @return array
     * @throws ApiException
     */
    public function batchCheckEmails(array $emails): array
    {
        try {
            $response = $this->httpClient->post('/batch-check', [
                'json' => [
                    'emails' => $emails
                ]
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
            
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Get API usage statistics
     * 
     * @param string $period Period: 'daily', 'weekly', 'monthly'
     * @return array
     * @throws ApiException
     */
    public function getUsageStats(string $period = 'daily'): array
    {
        try {
            $response = $this->httpClient->get('/v1/stats/usage', [
                'query' => [
                    'period' => $period
                ]
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
            
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Handle Guzzle exceptions
     * 
     * @param GuzzleException $e
     * @throws ApiException
     */
    private function handleException(GuzzleException $e): void
    {
        $response = $e->getResponse();
        
        if (!$response) {
            throw new ApiException('Network error: ' . $e->getMessage(), 0, $e);
        }
        
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true) ?? [];
        $message = $data['message'] ?? $data['error'] ?? 'Unknown error';
        
        switch ($statusCode) {
            case 401:
                throw new AuthenticationException($message, $statusCode, $e);
            case 429:
                throw new RateLimitException($message, $statusCode, $e);
            default:
                throw new ApiException($message, $statusCode, $e);
        }
    }
    
    /**
     * Set custom header
     * 
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Get current API token
     * 
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }
}