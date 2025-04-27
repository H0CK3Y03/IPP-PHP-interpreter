<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SOL25Object;
use IPP\Student\Exception;
use IPP\Student\Scope;

class Block
{
    public int $params_count;
    /** @var array<string> */
    public $params = [];
    /** @var array<Assignment> */
    public $instructions = [];

    public function __construct(int $params_count)
    {
        $this->params_count = $params_count;
    }

    /**
     * @param array<Message|Literal|Block|Variable|Method|Assignment|SOL25Object>|null $senderObj
     */
    public function evaluate(Scope $scope, ?array $senderObj = null): SOL25Object
    {
        $scope->enterScope();

        if ($senderObj == null && $this->params_count) {
            throw new Exception("Error: count of sending arguments doesn't match with arguments in block\n", ReturnCode::INTERPRET_DNU_ERROR);
        }
        if ($senderObj != null && $this->params_count != count($senderObj)) {
            throw new Exception("Error: count of sending arguments doesn't match with arguments in block\n", ReturnCode::INTERPRET_DNU_ERROR);
        }
        foreach ($this->params as $idx => $param) {
            if ($param && $senderObj == null) {
                $scope->addVar($param);
            } elseif ($senderObj) {
                if ($senderObj[$idx] instanceof SOL25Object) {
                    $scope->setVar($param, $senderObj[$idx]);
                } else {
                    $scope->setVar($param, $senderObj[$idx]->evaluate($scope));
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
