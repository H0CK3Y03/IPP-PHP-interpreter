<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\AstTree;

use IPP\Core\ReturnCode;
use IPP\Student\Sol25\SolObject;
use IPP\Student\InterDException;
use IPP\Student\Scopes;

class AstBlock
{
    public int $params_count;
    /** @var array<string> */
    public $params = [];
    /** @var array<AstAssignment> */
    public $instructions = [];

    public function __construct(int $params_count)
    {
        $this->params_count = $params_count;
    }

    /**
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment|SolObject>|null $senderObject
     */
    public function evaluate(Scopes $scope, ?array $senderObject = null): SolObject
    {
        $scope->enterScope();

        if ($senderObject == null && $this->params_count) {
            throw new InterDException("Error: count of sending arguments doesn't match with arguments in block\n", ReturnCode::INTERPRET_DNU_ERROR);
        }
        if ($senderObject != null && $this->params_count != count($senderObject)) {
            throw new InterDException("Error: count of sending arguments doesn't match with arguments in block\n", ReturnCode::INTERPRET_DNU_ERROR);
        }
        foreach ($this->params as $index => $param) {
            if ($param && $senderObject == null) {
                $scope->addVar($param);
            } elseif ($senderObject) {
                if ($senderObject[$index] instanceof SolObject) {
                    $scope->setVar($param, $senderObject[$index]);
                } else {
                    $scope->setVar($param, $senderObject[$index]->evaluate($scope));
                }
            }
        }

        foreach ($this->instructions as $instruction) {
            $lastResult = $instruction->evaluate($scope);
        }
        $scope->exitScope();
        return $lastResult ?? $scope->getSingleton('nil');
    }
}
