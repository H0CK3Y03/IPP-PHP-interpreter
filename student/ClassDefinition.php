<?php

namespace IPP\Student;

class ClassDefinition
{
    public string $name;
    public string $parent;

    /** @var array<string, Method> */
    public $methods = [];

    public function __construct(string $name, string $parent_name)
    {
        $this->name = $name;
        $this->parent = $parent_name;
    }
    // Add method to $this class
    public function addMethod(Method $method): void
    {
        $this->methods[$method->selector_name] = $method;
    }
}
