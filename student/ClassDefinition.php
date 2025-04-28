<?php

// Author: Adam Veselý
// Login: xvesela00
// File: ClassDefinition.php

namespace IPP\Student;

/**
 * Class representing a class definition with methods and an optional parent class.
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
     * @param string $parent Name of the parent class.
     */
    public function __construct(string $name, string $parent)
    {
        $this->setName($name);
        $this->setParent($parent);
    }

    /**
     * Adds a method to the class definition.
     *
     * @param Method $method The method to add.
     * @return void
     */
    public function addMethod(Method $method): void
    {
        $this->methods[$method->selectorName] = $method;
    }

    /**
     * Sets the name of the class.
     *
     * @param string $name Name of the class.
     */
    private function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Sets the parent class name.
     *
     * @param string $parent Name of the parent class.
     */
    private function setParent(string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Returns the list of methods in the class.
     *
     * @return array<string, Method> List of methods.
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Returns the name of the class.
     *
     * @return string Name of the class.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the name of the parent class.
     *
     * @return string Name of the parent class.
     */
    public function getParent(): string
    {
        return $this->parent;
    }
}
