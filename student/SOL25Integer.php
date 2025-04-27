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
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @return SOL25Object
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        $receiverVal = $receiverObj->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return new SOL25Object($scope->getClass($this->name), 0);
            case 'isNumber':
                return $this->boolResult(true, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(false, $scope);
            case 'isNil':
                return $this->boolResult(false, $scope);
            case 'identicalTo:':
                return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);
            case 'greaterThan:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                if ($receiverVal == $senderVal) {
                    return $this->boolResult(false, $scope);
                }
                return $this->boolResult($receiverVal > $senderVal, $scope);
            case 'plus:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->getClass('Integer'), $receiverVal + $senderVal);


            case 'minus:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');

                return new SOL25Object($scope->getClass('Integer'), (int) $receiverVal - (int) $senderVal);
            case 'multiplyBy:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->getClass('Integer'), (int) $receiverVal * (int) $senderVal);
            case 'divBy:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                if ((int) $senderVal == 0) {
                    fwrite(STDERR, "Zero division.\n");
                    exit(ReturnCode::INTERPRET_VALUE_ERROR);
                }
                return new SOL25Object($scope->getClass('Integer'), (int) $receiverVal / (int) $senderVal);
            case 'asString':
                return new SOL25Object($scope->getClass('String'), (string) $receiverVal);
            case 'asInteger':
                return $receiverObj;
            case 'timesRepeat:':
                for ($i = 1; $i <= $receiverVal; $i++) {
                    $iter = array(new SOL25Object($scope->getClass('Integer'), $i));
                    if ($senderObj[0] instanceof Block) {
                        $this->lastResult = $senderObj[0]->evaluate($scope, $iter);
                    } else {
                        $senderObj = $senderObj[0]->evaluate($scope);
                        $this->lastResult = $senderObj->class->getMethod('value:')['method']->block->evaluate($scope, $iter);
                    }
                }

                return $this->lastResult ?? $scope->getSingleton('nil');

            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
