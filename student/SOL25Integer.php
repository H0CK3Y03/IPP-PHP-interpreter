<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;
use IPP\Student\SOL25ObjectClass;
use IPP\Student\Exception;
use IPP\Student\Scope;

class SOL25Integer extends SOL25ObjectClass
{
    public ?SOL25Object $lastResult = null;

    /**
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObjs
     * @return SOL25Object
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObjs): SOL25Object
    {
        $receiverVal = $receiverObj->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return new SOL25Object($scope->getClass($this->name), 0);
            case 'isNumber':
                return $this->boolResult(true, $scope);
            case 'isString':
            case 'isBlock':
            case 'isNil':
                return $this->boolResult(false, $scope);
            case 'identicalTo:':
                return $this->boolResult($receiverObj->class === $senderObjs[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                // ==
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);
            case 'greaterThan:':
                // >
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal > $senderVal, $scope);
            case 'plus:':
                // +
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->getClass('Integer'), $receiverVal + $senderVal);
            case 'minus:':
                // -
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->getClass('Integer'), (int) $receiverVal - (int) $senderVal);
            case 'multiplyBy:':
                // *
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->getClass('Integer'), (int) $receiverVal * (int) $senderVal);
            case 'divBy:':
                // /
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return $this->handleDivision($receiverVal, $senderVal, $scope);
            case 'asString':
                return new SOL25Object($scope->getClass('String'), (string) $receiverVal);
            case 'asInteger':
                return $receiverObj;
            case 'timesRepeat:':
                return $this->handleTimesRepeat($senderObjs, $scope, $receiverVal);
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
    private function createNewIntegerObject(Scope $scope, int $value): SOL25Object
    {
        return new SOL25Object($scope->getClass('Integer'), $value);
    }
    private function handleDivision(int $receiverVal, int $senderVal, Scope $scope): SOL25Object
    {
        if ($senderVal === 0) {
            throw new Exception("Division by zero", ReturnCode::INTERPRET_VALUE_ERROR);
        }

        return $this->createNewIntegerObject($scope, $receiverVal / $senderVal);
    }
    private function handleTimesRepeat(?array $senderObjs, Scope $scope, int $receiverVal): SOL25Object
    {
        for ($i = 1; $i <= $receiverVal; $i++) {
            $iter = [new SOL25Object($scope->getClass('Integer'), $i)];
            $this->lastResult = $this->evaluateSender($senderObjs[0], $scope, $iter);
        }

        return $this->lastResult ?? $scope->getSingleton('nil');
    }

    private function evaluateSender($senderObj, Scope $scope, array $iter): SOL25Object
    {
        if ($senderObj instanceof Block) {
            return $senderObj->evaluate($scope, $iter);
        }

        $senderObj = $senderObj->evaluate($scope);
        return $senderObj->class->getMethod('value:')['method']->block->evaluate($scope, $iter);
    }
}

