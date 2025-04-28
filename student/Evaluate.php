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
     * Constructor to initialize the evaluation with a scope.
     *
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
        $mainClass = $this->getMainClass();
        $mainMethod = $this->getRunMethod($mainClass);

        // Create instances of 'Main' for 'self' and 'super'
        $this->initializeSelfAndSuper($mainClass);

        // Extract and evaluate the 'run' method's block
        $this->evaluateRunMethod($mainMethod);
    }

    /**
     * Fetch the 'Main' class from the scope.
     *
     * @return SOL25Class The 'Main' class.
     * @throws Exception If the 'Main' class cannot be found.
     */
    private function getMainClass(): SOL25Class
    {
        $mainClass = $this->scope->fetchClass('Main');
        return $mainClass;
    }

    /**
     * Fetch the 'run' method from the given class.
     *
     * @param SOL25Class $mainClass The class from which the 'run' method is fetched.
     * @return array{type: string, method: SOL25Method} The 'run' method information.
     * @throws Exception If the 'run' method is not found or not of type 'user'.
     */
    private function getRunMethod(SOL25Class $mainClass): array
    {
        $mainMethod = $mainClass->getMethod('run');
        if (!$mainMethod || $mainMethod['type'] !== 'user') {
            throw new Exception("Run method not found or not of type 'user' in class Main.", ReturnCode::INTERPRET_DNU_ERROR);
        }
        return $mainMethod;
    }

    /**
     * Initialize the 'self' and 'super' objects in the current scope.
     *
     * @param SOL25Class $mainClass The class to use for creating instances of 'self' and 'super'.
     */
    private function initializeSelfAndSuper(SOL25Class $mainClass): void
    {
        $mainSelfInstance = new SOL25Object($mainClass);
        $mainSuperInstance = new SOL25Object($mainClass->parent);

        $this->scope->setSelf($mainSelfInstance);
        $this->scope->setSuper($mainSuperInstance);
    }

    /**
     * Extract and evaluate the block of the 'run' method.
     *
     * @param array{type: string, method: SOL25Method} $mainMethod The 'run' method's information.
     */
    private function evaluateRunMethod(array $mainMethod): void
    {
        $mainMethodInstance = $mainMethod['method'];
        $mainBlock = $mainMethodInstance->block;

        $mainBlock->evaluate($this->scope);
    }
}
