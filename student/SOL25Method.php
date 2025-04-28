<?php

// Author: Adam Veselý
// Login: xvesela00
// File: SOL25Method.php

namespace IPP\Student;

use IPP\Student\Block;

/**
 * Represents a method, including its name, associated block of code, and parameters.
 */
class SOL25Method
{
    /** @var string The method's name */
    public string $name;
    
    /** @var Block The block of code for the method */
    public Block $block;
    
    /** @var array<string, string> The method's parameters, with their names and types */
    public array $params = [];

    /**
     * SOL25Method constructor.
     * Initializes the method with a name, block, and parameters.
     *
     * @param string $name The name of the method.
     * @param Block $block The block associated with the method.
     * @param array<string, string> $params The parameters of the method.
     */
    public function __construct(string $name, Block $block, array $params)
    {
        $this->setName($name);
        $this->setBlock($block);
        $this->setParameters($params);
    }

    /**
     * Set the method's name.
     *
     * @param string $name The method's name.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the method's name.
     *
     * @return string The method's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the method's block of code.
     *
     * @param Block $block The block of code.
     */
    public function setBlock(Block $block): void
    {
        $this->block = $block;
    }

    /**
     * Get the block of code associated with the method.
     *
     * @return Block The block associated with the method.
     */
    public function getBlock(): Block
    {
        return $this->block;
    }

    /**
     * Set or update the method's parameters.
     * This will overwrite any existing parameters.
     *
     * @param array<string, string> $params The parameters of the method.
     */
    public function setParameters(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Get the names of all the method's parameters.
     *
     * @return array<string> The parameter names.
     */
    public function getParameterNames(): array
    {
        return array_keys($this->params);
    }

    /**
     * Get the parameter types of the method.
     *
     * @return array<string> The parameter types.
     */
    public function getParameterTypes(): array
    {
        return array_values($this->params);
    }
}
