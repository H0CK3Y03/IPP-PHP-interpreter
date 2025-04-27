<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\Sol25;

use IPP\Core\ReturnCode;
use IPP\Student\AstTree\AstBlock;
use IPP\Student\AstTree\AstLiteral;
use IPP\Student\AstTree\AstMessage;
use IPP\Student\AstTree\AstMethod;
use IPP\Student\AstTree\AstVariable;
use IPP\Student\Sol25\SolObjectClass;
use IPP\Student\InterDException;
use IPP\Student\Scopes;

class SolIntegerClass extends SolObjectClass
{
    public ?SolObject $lastResult = null;

    /**
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|SolObject> $senderObjects
     * @return SolObject
     */
    public function switchMethod(SolObject $receiverObject, string $selectorName, Scopes $scope, ?array $senderObjects): SolObject
    {
        $receiverValue = $receiverObject->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return new SolObject($scope->getClass($this->name), 0);
            case 'isNumber':
                return $this->boolResult(true, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(false, $scope);
            case 'isNil':
                return $this->boolResult(false, $scope);
            case 'identicalTo:':
                return $this->boolResult($receiverObject->class === $senderObjects[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverValue == $senderValue, $scope);
            case 'greaterThan:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                if ($receiverValue == $senderValue) {
                    return $this->boolResult(false, $scope);
                }
                return $this->boolResult($receiverValue > $senderValue, $scope);
            case 'plus:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                return new SolObject($scope->getClass('Integer'), $receiverValue + $senderValue);


            case 'minus:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');

                return new SolObject($scope->getClass('Integer'), (int) $receiverValue - (int) $senderValue);
            case 'multiplyBy:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                return new SolObject($scope->getClass('Integer'), (int) $receiverValue * (int) $senderValue);
            case 'divBy:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                if ((int) $senderValue == 0) {
                    fwrite(STDERR, "Zero division.\n");
                    exit(ReturnCode::INTERPRET_VALUE_ERROR);
                }
                return new SolObject($scope->getClass('Integer'), (int) $receiverValue / (int) $senderValue);
            case 'asString':
                return new SolObject($scope->getClass('String'), (string) $receiverValue);
            case 'asInteger':
                return $receiverObject;
            case 'timesRepeat:':
                for ($x = 1; $x <= $receiverValue; $x++) {
                    $iterator = array(new SolObject($scope->getClass('Integer'), $x));
                    if ($senderObjects[0] instanceof AstBlock) {
                        $this->lastResult = $senderObjects[0]->evaluate($scope, $iterator);
                    } else {
                        $senderObject = $senderObjects[0]->evaluate($scope);
                        $this->lastResult = $senderObject->class->getMethod('value:')['method']->block->evaluate($scope, $iterator);
                    }
                }

                return $this->lastResult ?? $scope->getSingleton('nil');

            default:
                throw new InterDException('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
