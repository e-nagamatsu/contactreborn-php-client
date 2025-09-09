<?php
/**
 * Basic usage example for Contact/Reborn API Client
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContactReborn\ContactRebornClient;
use ContactReborn\Enums\CheckResult;
use ContactReborn\Enums\BlockType;
use ContactReborn\Exceptions\ApiException;
use ContactReborn\Exceptions\AuthenticationException;
use ContactReborn\Exceptions\RateLimitException;

// Replace with your actual API token
$apiToken = 'your-api-token-here';

try {
    // Initialize the client
    $client = new ContactRebornClient($apiToken);
    
    echo "=== Contact/Reborn API Client Example ===\n\n";
    
    // 1. Check single email
    echo "1. Checking single email...\n";
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
    
    // 2. Batch check multiple emails
    echo "2. Batch checking multiple emails...\n";
    $emails = [
        'user1@example.com',
        'user2@example.com',
        'blocked@tempmail.com'
    ];
    
    $results = $client->batchCheckEmails($emails);
    foreach ($results['results'] as $emailResult) {
        $status = CheckResult::getLabel($emailResult['result'] ?? CheckResult::UNKNOWN);
        $icon = CheckResult::isBlocked($emailResult['result']) ? '❌' : 
                (CheckResult::isSafe($emailResult['result']) ? '✅' : '⚠️');
        echo "  {$icon} {$emailResult['email']}: {$status}\n";
    }
    echo "\n";
    
    // 3. Manage blocked emails
    echo "3. Managing blocked emails...\n";
    
    // Get current blocked list
    $blockedList = $client->getBlockedEmails(1, 10);
    echo "Current blocked emails: {$blockedList['total']} total\n";
    
    // Add a new blocked email with type information
    $newBlocked = $client->addBlockedEmail(
        'blocked@example.com',
        '不正なメール送信者'
    );
    // Note: In a full implementation, you might specify the block type:
    // BlockType::FULL - for exact email match
    // BlockType::PREFIX - for prefix match (e.g., 'noreply@*')
    // BlockType::SUFFIX - for domain match (e.g., '*@temporary-email.com')
    echo "Added blocked email ID: {$newBlocked['id']}\n";
    
    // Remove blocked email (uncomment to test)
    // $removed = $client->removeBlockedEmail($newBlocked['id']);
    // echo "Removed blocked email: " . ($removed ? 'Success' : 'Failed') . "\n";
    
    echo "\n";
    
    // 4. Get usage statistics
    echo "4. Getting usage statistics...\n";
    $stats = $client->getUsageStats('daily');
    echo "Today's API calls: {$stats['calls_today']}\n";
    echo "Remaining calls: {$stats['remaining_calls']}\n";
    echo "Rate limit: {$stats['rate_limit']}\n";
    
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