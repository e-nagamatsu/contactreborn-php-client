<?php

namespace ContactReborn\Exceptions;

/**
 * Rate Limit Exception
 * 
 * Thrown when API rate limit is exceeded
 */
class RateLimitException extends ApiException
{
    /**
     * @var int|null Retry after seconds
     */
    protected $retryAfter;
    
    /**
     * Set retry after seconds
     * 
     * @param int $seconds
     * @return self
     */
    public function setRetryAfter(int $seconds): self
    {
        $this->retryAfter = $seconds;
        return $this;
    }
    
    /**
     * Get retry after seconds
     * 
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}