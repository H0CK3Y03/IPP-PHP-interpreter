<?php

namespace IPP\Student;

class Method
{
    public string $selector_name;
    public Block $method_body;

    public function __construct(string $selector, Block $body)
    {
        $this->selector_name = $selector;
        $this->method_body = $body;
    }
}
