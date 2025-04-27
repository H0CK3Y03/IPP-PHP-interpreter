<?php

namespace IPP\Student;

/**
 * Represents a method definition with a selector name and a body (block of code).
 */
class Method
{
    /**
     * The selector (method name) associated with this method.
     *
     * @var string
     */
    public string $selectorName;

    /**
     * The body of the method as a block.
     *
     * @var Block
     */
    public Block $body;

    /**
     * Method constructor.
     *
     * @param string $selectorName Name of the method (selector).
     * @param Block $body Block containing method instructions.
     */
    public function __construct(string $selectorName, Block $body)
    {
        $this->selectorName = $selectorName;
        $this->body = $body;
    }
}
