<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Method.php

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
        $this->setSelectorName($selectorName);
        $this->setBlock($block);
    }

    /**
     * Sets the selector (method name) for the method.
     *
     * @param string $selectorName Name of the method.
     */
    private function setSelectorName(string $selectorName): void
    {
        $this->selectorName = $selectorName;
    }

    /**
     * Sets the body block for the method.
     *
     * @param Block $block Block containing method instructions.
     */
    private function setBlock(Block $block): void
    {
        $this->block = $block;
    }

    /**
     * Gets the selector (method name) for the method.
     *
     * @return string Method selector name.
     */
    public function getSelectorName(): string
    {
        return $this->selectorName;
    }

    /**
     * Gets the body block of the method.
     *
     * @return Block Method body block.
     */
    public function getBlock(): Block
    {
        return $this->block;
    }
}
