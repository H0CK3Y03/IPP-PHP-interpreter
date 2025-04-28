<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Block.php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SOL25Object;
use IPP\Student\Exception;
use IPP\Student\Scope;

/**
 * Represents a block of instructions with optional parameters.
 */
class Block
{
    /**
     * Number of parameters the block expects.
     *
     * @var int
     */
    public int $paramCount;

    /**
     * List of parameter names.
     *
     * @var string[]
     */
    public array $params = [];

    /**
     * List of instructions within the block.
     *
     * @var Assignment[]
     */
    public array $instructions = [];

    /**
     * Create a new block with a given number of parameters.
     *
     * @param int $paramCount
     */
    public function __construct(int $paramCount)
    {
        $this->paramCount = $paramCount;
    }

    /**
     * Evaluates the block within a new scope, with optional args.
     *
     * @param Scope $scope
     * @param array<Message|Literal|Block|Variable|Method|Assignment|SOL25Object>|null $args
     * @return SOL25Object
     *
     * @throws Exception if the number of args doesn't match the number of parameters
     */
    public function evaluate(Scope $scope, ?array $args = null): SOL25Object
    {
        $scope->startNewScope();

        $this->validateargs($args);

        $this->bindParameters($scope, $args);

        $lastResult = $this->executeInstructions($scope);

        $scope->endCurrentScope();

        return $lastResult ?? $scope->fetchSingleton('nil');
    }

    /**
     * Validates that the number of passed args matches the number of expected parameters.
     *
     * @param array<int, Message|Literal|Block|Variable|Method|Assignment|SOL25Object>|null $args
     * @throws Exception
     */
    private function validateargs(?array $args): void
    {
        if (($args === null && $this->paramCount > 0) || ($args !== null && count($args) !== $this->paramCount)) {
            throw new Exception("Number of sent args (" . ($args !== null ? count($args) : 'null') . ") doesn't match the number of parameters in the block (" . $this->paramCount . ")\n", ReturnCode::INTERPRET_DNU_ERROR);
        }
    }

    /**
     * Binds parameters in the scope, either by adding them or setting their value from args.
     *
     * @param Scope $scope
     * @param array<int, Message|Literal|Block|Variable|Method|Assignment|SOL25Object>|null $args
     */
    private function bindParameters(Scope $scope, ?array $args): void
    {
        foreach ($this->params as $index => $paramName) {
            if ($args === null) {
                $scope->addVariable($paramName);
            }
            else {
                $arg = $args[$index];
                $value = ($arg instanceof SOL25Object) ? $arg : $arg->evaluate($scope);
                $scope->assignVariable($paramName, $value);
            }
        }
    }

    /**
     * Executes the instructions in order and returns the result of the last instruction.
     *
     * @param Scope $scope
     * @return SOL25Object|null
     */
    private function executeInstructions(Scope $scope): ?SOL25Object
    {
        $result = null;
        foreach ($this->instructions as $instruction) {
            $result = $instruction->evaluate($scope);
        }
        return $result;
    }
}
