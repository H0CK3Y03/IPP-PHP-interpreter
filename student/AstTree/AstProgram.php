<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\AstTree;

class AstProgram
{
    /** @var array<string, AstClassDefinition> */
    public $classes_list = [];

    // Add a class definition to the program
    public function addClass(AstClassDefinition $class): void
    {
        $this->classes_list[$class->name] = $class;
    }
}
