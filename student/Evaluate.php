<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SOL25Object;
use IPP\Student\Scope;
use IPP\Student\Exception;

/**
 * Handles the evaluation of the program starting from the Main class and its 'run' method.
 */
class Evaluate
{
    private Scope $scope;

    /**
     * @param Scope $scope The scope object where the evaluation occurs.
     */
    public function __construct(Scope $scope)
    {
        $this->scope = $scope;
    }

    /**
     * Starts the evaluation from the 'Main' class and its 'run' method.
     *
     * @throws Exception If the 'run' method is not found or has an incorrect type.
     */
    public function evaluate(): void
    {
        // Retrieve the 'Main' class from the scope
        $mainClass = $this->scope->fetchClass('Main');

        // Retrieve the 'run' method from the 'Main' class
        $mainMethod = $mainClass->getMethod('run');

        // Check if the 'run' method exists and is of type 'user'
        if (!$mainMethod || $mainMethod['type'] !== 'user') {
            throw new Exception("Run method not found or not of type 'user' in class Main.", ReturnCode::INTERPRET_DNU_ERROR);
        }

        // Create instances of 'Main' for 'self' and 'super'
        $mainSelfInstance = new SOL25Object($mainClass);
        $mainSuperInstance = new SOL25Object($mainClass->parent);

        // Set the 'self' and 'super' in the current scope
        $this->scope->setSelf($mainSelfInstance);
        $this->scope->setSuper($mainSuperInstance);

        // Extract the method's block and evaluate it
        $mainMethodInstance = $mainMethod['method'];
        $mainBlock = $mainMethodInstance->block;

        $mainBlock->evaluate($this->scope);
    }
}
