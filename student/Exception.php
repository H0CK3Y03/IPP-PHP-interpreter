<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

/**
 * Custom exception class for handling specific errors within the IPP Student project.
 */
class Exception extends IPPException
{
    /**
     * The return code associated with the exception.
     *
     * @var int
     */
    private int $returnCode;

    /**
     * Exception constructor.
     *
     * @param string $message The error message.
     * @param int $returnCode The return code associated with the exception.
     * @param \Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        string $message = "Unexpected behavior",
        int $returnCode = ReturnCode::INPUT_FILE_ERROR,
        ?\Throwable $previous = null
    ) {
        // Call the parent constructor to set message and return code
        parent::__construct($message, $returnCode, $previous);

        // Set the return code for the exception
        $this->setReturnCode($returnCode);
    }

    /**
     * Sets the return code for the exception.
     *
     * @param int $returnCode The return code associated with the exception.
     */
    private function setReturnCode(int $returnCode): void
    {
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
