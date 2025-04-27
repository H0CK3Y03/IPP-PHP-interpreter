<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class Exception extends IPPException
{
    protected int $returnCode;

    public function __construct(
        string $msg = "Unexpected behavior",
        int $code = ReturnCode::INPUT_FILE_ERROR,
        ?\Throwable $prev = null
    ) {
        parent::__construct($msg, $code, $prev);
        $this->returnCode = $code;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }
}
