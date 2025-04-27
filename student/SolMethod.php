<?php

namespace IPP\Student;

use IPP\Student\AstBlock;

class SolMethod
{
    public string $name;
    public AstBlock $block;
    /** @var array<string, string> */
    public array $parameters = [];

    /**
     * @param array<string, string> $params
     */
    public function __construct(string $name, AstBlock $block, array $params)
    {
        $this->name = $name;
        $this->block = $block;
        $this->parameters = $params;
    }

    // Get the block of the method
    public function getBlock(): AstBlock
    {
        return $this->block;
    }

    /**
     * Set the parameters for the method
     * @param array<string, string> $params
     */
    public function setParameters(array $params): void
    {
        $this->parameters = $params;
    }

    /**
     * Get the names of the method parameters
     * @return array<string, string>
     */
    public function getParameterNames(): array
    {
        return $this->parameters;
    }
}
