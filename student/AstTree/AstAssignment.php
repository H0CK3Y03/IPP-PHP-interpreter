<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\AstTree;

use IPP\Student\AstTree\AstBlock;
use IPP\Student\AstTree\AstLiteral;
use IPP\Student\AstTree\AstMessage;
use IPP\Student\AstTree\AstMethod;
use IPP\Student\AstTree\AstVariable;
use IPP\Student\Sol25\SolObject;
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
