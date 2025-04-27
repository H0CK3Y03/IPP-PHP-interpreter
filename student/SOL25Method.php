<?php

namespace IPP\Student;

use IPP\Student\Block;

class SOL25Method
{
    public string $name;
    public Block $block;
    /** @var array<string, string> */
    public array $params = [];

    /**
     * @param array<string, string> $params
     */
    public function __construct(string $name, Block $block, array $params)
    {
        $this->name = $name;
        $this->block = $block;
        $this->params = $params;
    }

    // Get the block of the method
    public function getBlock(): Block
    {
        return $this->block;
    }

    /**
     * Set the params for the method
     * @param array<string, string> $params
     */
    public function setparams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Get the names of the method params
     * @return array<string, string>
     */
    public function getParameterNames(): array
    {
        return $this->params;
    }
}
