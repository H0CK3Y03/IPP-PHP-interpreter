<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SOL25Object;
use IPP\Student\Scope;

class Evaluate
{
    public Scope $scope;

    public function __construct(Scope $scope)
    {
        $this->scope = $scope;
    }

    // Start evaluation from the Main class and its run method
    public function evaluate(): void
    {
        $mainClass = $this->scope->getClass('Main');

        $mainMethod = $mainClass->getMethod('run');

        if (!$mainMethod || $mainMethod['type'] !== 'user') {
            throw new Exception("Run method not found in class Main\n", ReturnCode::INTERPRET_DNU_ERROR);
        }

        // Create a new instance of Main class for self and super
        $mainSelfInstance = new SOL25Object($mainClass);
        $mainSuperInstance = new SOL25Object($mainClass->parent);

        // Set self and super in the scope
        $this->scope->setSelf($mainSelfInstance);
        $this->scope->setSuper($mainSuperInstance);

        // Extract method
        $mainMethod = $mainMethod['method'];
        $mainBlock = $mainMethod->block;

        $mainBlock->evaluate($this->scope);
    }
}
