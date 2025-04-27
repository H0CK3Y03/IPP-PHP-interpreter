<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\AstTree;

class AstClassDefinition
{
    public string $name;
    public string $parent;

    /** @var array<string, AstMethod> */
    public $methods_list = [];

    public function __construct(string $name, string $parent_name)
    {
        $this->name = $name;
        $this->parent = $parent_name;
    }
    // Add method to $this class
    public function addMethod(AstMethod $method): void
    {
        $this->methods_list[$method->selector_name] = $method;
    }
}
