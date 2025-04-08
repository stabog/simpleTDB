<?php

namespace SimpleTdb;

/**
 * Custom exception class for TextDataModel errors
 */
class TextDataModelException extends \Exception 
{
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
}