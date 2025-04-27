<?php

namespace IPP\Student;

class Program
{
    /** @var array<string, AstClassDefinition> */
    public $classes = [];

    // Add a class definition to the program
    public function addClass(ClassDefinition $class): void
    {
        $this->classes[$class->name] = $class;
    }
}
