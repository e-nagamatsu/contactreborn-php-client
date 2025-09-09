<?php
/**
 * Basic usage example for Contact/Reborn API Client
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContactReborn\ContactRebornClient;
use ContactReborn\Enums\CheckResult;
use ContactReborn\Exceptions\ApiException;
use ContactReborn\Exceptions\AuthenticationException;
use ContactReborn\Exceptions\RateLimitException;

// Replace with your actual API token
$apiToken = 'your-api-token-here';

try {
    // Initialize the client
    $client = new ContactRebornClient($apiToken);
    
    echo "=== Contact/Reborn API Client Example ===\n\n";
    
    // Check single email
    echo "Checking email...\n";
    $email = 'test@example.com';
    $result = $client->checkEmail($email);
    
    echo "Email: {$email}\n";
    
    // New enum-based approach (recommended)
    echo "Result: " . CheckResult::getLabel($result['result']) . "\n";
    echo "Status: " . CheckResult::getDescription($result['result']) . "\n";
    
    if (CheckResult::isBlocked($result['result'])) {
        echo "⚠️ This email is BLOCKED\n";
        if ($result['reason']) {
            echo "Reason: {$result['reason']}\n";
        }
    } elseif (CheckResult::isSafe($result['result'])) {
        echo "✅ This email is SAFE\n";
    } elseif (CheckResult::needsReview($result['result'])) {
        echo "🔍 This email needs REVIEW\n";
    }
    
    // Legacy fields (for backward compatibility)
    echo "Is Blocked (legacy): " . ($result['is_blocked'] ? 'Yes' : 'No') . "\n";
    echo "Confidence: {$result['confidence']}\n";
    echo "Checked at: {$result['checked_at']}\n\n";
    
} catch (AuthenticationException $e) {
    echo "ERROR: Authentication failed. Please check your API token.\n";
    echo "Message: " . $e->getMessage() . "\n";
    
} catch (RateLimitException $e) {
    echo "ERROR: Rate limit exceeded.\n";
    echo "Message: " . $e->getMessage() . "\n";
    if ($e->getRetryAfter()) {
        echo "Retry after: {$e->getRetryAfter()} seconds\n";
    }
    
} catch (ApiException $e) {
    echo "ERROR: API request failed.\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    
} catch (Exception $e) {
    echo "ERROR: Unexpected error occurred.\n";
    echo "Message: " . $e->getMessage() . "\n";
}

echo "\n=== End of example ===\n";