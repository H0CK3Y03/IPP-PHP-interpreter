<?php

namespace IPP\Student;

/**
 * Represents a collection of class definitions forming the program.
 */
class Program
{
    /**
     * List of all class definitions in the program, indexed by class name.
     *
     * @var array<string, ClassDefinition>
     */
    public array $classes = [];

    /**
     * Adds a class definition to the program.
     *
     * @param ClassDefinition $class
     */
    public function addClass(ClassDefinition $class): void
    {
        $this->classes[$class->name] = $class;
    }
}
