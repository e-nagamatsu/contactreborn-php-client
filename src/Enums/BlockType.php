<?php

namespace ContactReborn\Enums;

/**
 * Email block type enum
 * 
 * @package ContactReborn\Enums
 */
class BlockType
{
    /**
     * Full email address match
     */
    const FULL = 'full';
    
    /**
     * Email prefix match (e.g., test@*)
     */
    const PREFIX = 'prefix';
    
    /**
     * Email suffix/domain match (e.g., *@example.com)
     */
    const SUFFIX = 'suffix';
    
    /**
     * Domain-only match
     */
    const DOMAIN = 'domain';
    
    /**
     * Pattern/regex match
     */
    const PATTERN = 'pattern';
    
    /**
     * Get human readable label for block type
     * 
     * @param string $type
     * @return string
     */
    public static function getLabel(string $type): string
    {
        $labels = [
            self::FULL => 'Full Match',
            self::PREFIX => 'Prefix Match',
            self::SUFFIX => 'Domain Match',
            self::DOMAIN => 'Domain Only',
            self::PATTERN => 'Pattern Match'
        ];
        
        return $labels[$type] ?? 'Unknown';
    }
    
    /**
     * Get description for block type
     * 
     * @param string $type
     * @return string
     */
    public static function getDescription(string $type): string
    {
        $descriptions = [
            self::FULL => 'Blocks exact email address',
            self::PREFIX => 'Blocks all emails starting with this prefix',
            self::SUFFIX => 'Blocks all emails from this domain',
            self::DOMAIN => 'Blocks entire domain',
            self::PATTERN => 'Blocks emails matching pattern'
        ];
        
        return $descriptions[$type] ?? 'No description available.';
    }
    
    /**
     * Get all block types
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            self::FULL,
            self::PREFIX,
            self::SUFFIX,
            self::DOMAIN,
            self::PATTERN
        ];
    }
    
    /**
     * Validate if a value is a valid block type
     * 
     * @param string $type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::all());
    }
}