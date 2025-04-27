<?php
/**
 * @author Miroslav Basista (xbasism00)
 */
namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class InterDException extends IPPException
{
    protected int $returnCode;

    public function __construct(
        string $message = "Unexpected behavior",
        int $code = ReturnCode::INPUT_FILE_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->returnCode = $code;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }
}
