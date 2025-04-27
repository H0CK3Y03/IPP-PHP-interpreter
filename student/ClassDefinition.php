<?php

namespace IPP\Student;

/**
 * Represents a class definition with methods and an optional parent class.
 */
class ClassDefinition
{
    /**
     * Name of the class.
     *
     * @var string
     */
    public string $name;

    /**
     * Name of the parent class.
     *
     * @var string
     */
    public string $parent;

    /**
     * List of methods defined in this class, indexed by selector name.
     *
     * @var array<string, Method>
     */
    public array $methods = [];

    /**
     * ClassDefinition constructor.
     *
     * @param string $name Name of the class.
     * @param string $parentName Name of the parent class.
     */
    public function __construct(string $name, string $parentName)
    {
        $this->name = $name;
        $this->parent = $parentName;
    }

    /**
     * Adds a method to the class definition.
     *
     * @param Method $method The method to add.
     */
    public function addMethod(Method $method): void
    {
        $this->methods[$method->selector_name] = $method;
    }
}
