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
        $this->setClass($class);
    }

    /**
     * Sets the class definition in the program.
     *
     * @param ClassDefinition $class
     */
    private function setClass(ClassDefinition $class): void
    {
        $this->classes[$class->name] = $class;
    }

    /**
     * Gets the list of all class definitions.
     *
     * @return array<string, ClassDefinition> List of class definitions.
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Gets a class definition by name.
     *
     * @param string $className
     * @return ClassDefinition|null The class definition, or null if not found.
     */
    public function getClass(string $className): ?ClassDefinition
    {
        return $this->classes[$className] ?? null;
    }
}
