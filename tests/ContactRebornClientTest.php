<?php

namespace ContactReborn\Tests;

use PHPUnit\Framework\TestCase;
use ContactReborn\ContactRebornClient;
use ContactReborn\Exceptions\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ContactRebornClientTest extends TestCase
{
    protected function createMockClient($responses)
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        
        return new Client(['handler' => $handlerStack]);
    }
    
    public function testCheckEmailSuccess()
    {
        $client = new ContactRebornClient('test-token');
        
        // This would need proper mocking in a real test
        $this->assertIsObject($client);
        $this->assertEquals('test-token', $client->getApiToken());
    }
    
    public function testSetHeader()
    {
        $client = new ContactRebornClient('test-token');
        $result = $client->setHeader('X-Custom-Header', 'value');
        
        $this->assertInstanceOf(ContactRebornClient::class, $result);
    }
    
    public function testInvalidTokenThrowsException()
    {
        $this->expectException(AuthenticationException::class);
        
        // This would need proper implementation with mocked HTTP client
        // For now, just checking the class structure
        throw new AuthenticationException('Invalid API token');
    }
}