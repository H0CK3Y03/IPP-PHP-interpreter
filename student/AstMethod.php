<?php

namespace IPP\Student;

class AstMethod
{
    public string $selector_name;
    public AstBlock $method_body;

    public function __construct(string $selector, AstBlock $body)
    {
        $this->selector_name = $selector;
        $this->method_body = $body;
    }
}
