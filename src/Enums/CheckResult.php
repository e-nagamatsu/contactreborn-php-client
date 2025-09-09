<?php

namespace ContactReborn\Enums;

/**
 * Email check result enum
 * 
 * @package ContactReborn\Enums
 */
class CheckResult
{
    /**
     * Email passed all checks
     */
    const PASS = 'pass';
    
    /**
     * Email is blocked
     */
    const BLOCK = 'block';
    
    /**
     * Email is suspicious but not definitively blocked
     */
    const SUSPICIOUS = 'suspicious';
    
    /**
     * Unable to verify email
     */
    const UNKNOWN = 'unknown';
    
    /**
     * Check if result indicates the email should be blocked
     * 
     * @param string $result
     * @return bool
     */
    public static function isBlocked(string $result): bool
    {
        return $result === self::BLOCK;
    }
    
    /**
     * Check if result indicates the email is safe
     * 
     * @param string $result
     * @return bool
     */
    public static function isSafe(string $result): bool
    {
        return $result === self::PASS;
    }
    
    /**
     * Check if result requires manual review
     * 
     * @param string $result
     * @return bool
     */
    public static function needsReview(string $result): bool
    {
        return in_array($result, [self::SUSPICIOUS, self::UNKNOWN]);
    }
    
    /**
     * Get human readable label for result
     * 
     * @param string $result
     * @return string
     */
    public static function getLabel(string $result): string
    {
        $labels = [
            self::PASS => 'Safe',
            self::BLOCK => 'Blocked',
            self::SUSPICIOUS => 'Suspicious',
            self::UNKNOWN => 'Unknown'
        ];
        
        return $labels[$result] ?? 'Unknown';
    }
    
    /**
     * Get description for result
     * 
     * @param string $result
     * @return string
     */
    public static function getDescription(string $result): string
    {
        $descriptions = [
            self::PASS => 'This email address has passed all validation checks.',
            self::BLOCK => 'This email address has been blocked due to policy violations.',
            self::SUSPICIOUS => 'This email address shows suspicious patterns and should be reviewed.',
            self::UNKNOWN => 'Unable to verify this email address.'
        ];
        
        return $descriptions[$result] ?? 'No description available.';
    }
    
    /**
     * Get all possible results
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            self::PASS,
            self::BLOCK,
            self::SUSPICIOUS,
            self::UNKNOWN
        ];
    }
    
    /**
     * Validate if a value is a valid result
     * 
     * @param string $result
     * @return bool
     */
    public static function isValid(string $result): bool
    {
        return in_array($result, self::all());
    }
}