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
    public Block $block;

    /**
     * Method constructor.
     *
     * @param string $selectorName Name of the method (selector).
     * @param Block $block Block containing method instructions.
     */
    public function __construct(string $selectorName, Block $block)
    {
        $this->selectorName = $selectorName;
        $this->block = $block;
    }
}
