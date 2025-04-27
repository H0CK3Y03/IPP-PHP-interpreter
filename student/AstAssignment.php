<?php

namespace IPP\Student;

use IPP\Student\AstBlock;
use IPP\Student\AstLiteral;
use IPP\Student\AstMessage;
use IPP\Student\AstMethod;
use IPP\Student\AstVariable;
use IPP\Student\SolObject;
use IPP\Student\Scopes;

class AstAssignment
{
    public string $variable;

    /**
     * @var AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment
     */
    public AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment $expression;

    public function __construct(
        string $variable,
        AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment $expression
    ) {
        $this->variable = $variable;
        $this->expression = $expression;
    }

    public function evaluate(Scopes $scope): SolObject
    {
        $execute = false;
        if ($this->variable == '_') {
            $execute = true;
        }

        if ($this->variable != '' && !$execute) {
            if (!$scope->hasVar($this->variable)) {
                $scope->addVar($this->variable);
            }
        }

        if ($this->expression instanceof AstBlock) {
            $blockObject = new SolObject($scope->getClass('Block'), $this->expression);
            $scope->setVar($this->variable, $blockObject);
            return $blockObject;
        } else {
            $ExprValue = $this->expression->evaluate($scope);
            if ($ExprValue != null && !$execute) {
                $scope->setVar($this->variable, $ExprValue);
            }
            return $ExprValue;
        }
    }
}
