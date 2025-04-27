<?php

namespace IPP\Student;

use IPP\Student\Block;

class SOL25Method
{
    // The name of the method
    public string $name;
    
    // The block of code associated with the method
    public Block $block;
    
    // Associative array of parameters where the key is the param name and the value is the param type
    /** @var array<string, string> */
    public array $params = [];

    /**
     * Constructor to initialize the method's name, block, and parameters.
     *
     * @param string $name The name of the method.
     * @param Block $block The block associated with the method.
     * @param array<string, string> $params The parameters of the method.
     */
    public function __construct(string $name, Block $block, array $params)
    {
        $this->name = $name;
        $this->block = $block;
        $this->params = $params;
    }

    /**
     * Get the block of the method.
     *
     * @return Block The block of the method.
     */
    public function getBlock(): Block
    {
        return $this->block;
    }

    /**
     * Set the parameters for the method.
     * This will overwrite any existing parameters.
     *
     * @param array<string, string> $params The new parameters for the method.
     */
    public function setParameters(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Get the names of the method parameters.
     *
     * @return array<string> The names of the method parameters.
     */
    public function getParameterNames(): array
    {
        return array_keys($this->params);
    }
}
