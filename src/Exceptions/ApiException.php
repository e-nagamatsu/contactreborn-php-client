<?php

namespace ContactReborn\Exceptions;

use Exception;

/**
 * Base API Exception
 */
class ApiException extends Exception
{
    /**
     * @var array|null Response data
     */
    protected $responseData;
    
    /**
     * Constructor
     * 
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param array|null $responseData
     */
    public function __construct(
        string $message = "", 
        int $code = 0, 
        Exception $previous = null,
        array $responseData = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->responseData = $responseData;
    }
    
    /**
     * Get response data
     * 
     * @return array|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}