<?php

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
     * Evaluates the block within a new scope, with optional arguments.
     *
     * @param Scope $scope
     * @param array<Message|Literal|Block|Variable|Method|Assignment|SOL25Object>|null $arguments
     * @return SOL25Object
     *
     * @throws Exception if the number of arguments doesn't match the number of parameters
     */
    public function evaluate(Scope $scope, ?array $arguments = null): SOL25Object
    {
        $scope->enterScope();

        $this->validateArguments($arguments);

        $this->bindParameters($scope, $arguments);

        $lastResult = $this->executeInstructions($scope);

        $scope->exitScope();

        return $lastResult ?? $scope->getSingleton('nil');
    }

    /**
     * Validates that the number of passed arguments matches the number of expected parameters.
     *
     * @param array|null $arguments
     * @throws Exception
     */
    private function validateArguments(?array $arguments): void
    {
        if (($arguments === null && $this->paramCount > 0) || ($arguments !== null && count($arguments) !== $this->paramCount)) {
            throw new Exception(
                "Error: Count of sending arguments doesn't match the number of parameters in the block\n",
                ReturnCode::INTERPRET_DNU_ERROR
            );
        }
    }

    /**
     * Binds parameters in the scope, either by adding them or setting their value from arguments.
     *
     * @param Scope $scope
     * @param array|null $arguments
     */
    private function bindParameters(Scope $scope, ?array $arguments): void
    {
        foreach ($this->params as $index => $paramName) {
            if ($arguments === null) {
                $scope->addVar($paramName);
            }
            else {
                $argument = $arguments[$index];
                $value = ($argument instanceof SOL25Object) ? $argument : $argument->evaluate($scope);
                $scope->setVar($paramName, $value);
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
