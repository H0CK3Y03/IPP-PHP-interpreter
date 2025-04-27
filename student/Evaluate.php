<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Sol25\SolObject;
use IPP\Student\Scopes;

class Evaluate
{
    public Scopes $scope;

    public function __construct(Scopes $scope)
    {
        $this->scope = $scope;
    }

    // Start evaluation from the Main class and its run method
    public function evaluate(): void
    {
        $mainClass = $this->scope->getClass('Main');

        $mainMethod = $mainClass->getMethod('run');

        if (!$mainMethod || $mainMethod['type'] !== 'user') {
            throw new InterDException("Run method not found in class Main\n", ReturnCode::INTERPRET_DNU_ERROR);
        }

        // Create a new instance of Main class for self and super
        $mainSelfInstance = new SolObject($mainClass);
        $mainSuperInstance = new SolObject($mainClass->parent);

        // Set self and super in the scope
        $this->scope->setSelf($mainSelfInstance);
        $this->scope->setSuper($mainSuperInstance);

        // Extract method
        $mainMethod = $mainMethod['method'];
        $mainBlock = $mainMethod->block;

        $mainBlock->evaluate($this->scope);
    }
}
