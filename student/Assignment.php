<?php

namespace IPP\Student;

use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;
use IPP\Student\SOL25Object;
use IPP\Student\Scope;

class Assignment
{
    public string $var;
    /**
     * The expression being assigned to the variable.
     * It can be an instance of any of the following types:
     * Message, Literal, Block, Variable, Method, or another Assignment.
     */
    public Message|Literal|Block|Variable|Method|Assignment $expr;

    /**
     * Constructor for Assignment.
     *
     * @param string $variable The variable being assigned a value.
     * @param Message|Literal|Block|Variable|Method|Assignment $expression The expression whose result is assigned to the variable.
     */
    public function __construct(string $var, Message|Literal|Block|Variable|Method|Assignment $expr)
    {
        $this->var = $var;
        $this->expr = $expr;
    }

    /**
     * Evaluates the expression and assigns its result to the variable in the given scope.
     *
     * @param Scope $scope The current variable scope.
     * @return SOL25Object The result of the evaluation of the expression.
     */
    public function evaluate(Scope $scope): SOL25Object
    {
        // If the variable is an underscore, we don't assign the result
        if ($this->var === '_') {
            return $this->evaluateExpression($scope);
        }

        // Ensure the variable exists in the scope
        if (!$scope->hasVar($this->var)) {
            $scope->addVar($this->var);
        }

        // Evaluate the expression and assign its value to the variable
        $exprVal = $this->evaluateExpression($scope);
        if ($exprVal !== null) {
            $scope->setVar($this->var, $exprVal);
        }

        return $exprVal;
    }

    /**
     * Helper method to evaluate the expression, which can be any supported AST type.
     *
     * @param Scope $scope The current variable scope.
     * @return SOL25Object|null The evaluated result of the expression.
     */
    private function evaluateExpression(Scope $scope): ?SOL25Object
    {
        if ($this->expr instanceof Block) {
            $blockObj = new SOL25Object($scope->getClass('Block'), $this->expr);
            return $blockObj;
        }

        return $this->expr->evaluate($scope);
    }
}
