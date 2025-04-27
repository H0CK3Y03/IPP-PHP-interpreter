<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

/**
 * Custom exception class for handling specific errors within the IPP Student project.
 */
class Exception extends IPPException
{
    /** @var int The return code associated with the exception. */
    protected int $returnCode;

    /**
     * Exception constructor.
     *
     * @param string $message The error message.
     * @param int $returnCode The return code associated with the exception.
     * @param \Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(string $message = "Unexpected behavior", int $returnCode = ReturnCode::INPUT_FILE_ERROR, ?\Throwable $previous = null)
    {
        // Call parent constructor with the provided message and return code
        parent::__construct($message, $returnCode, $previous);

        $this->returnCode = $returnCode;
    }

    /**
     * Get the return code associated with the exception.
     *
     * @return int The return code for the exception.
     */
    public function getReturnCode(): int
    {
        return $this->returnCode;
    }
}
