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

class SOL25Nil extends SOL25ObjectClass
{
    /**
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @return SOL25Object
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        /** @var string|int|bool $receiverVal */
        $receiverVal = $receiverObj->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return $scope->getSingleton('nil');
            case 'identicalTo:':
                return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);
            case 'asString':
                return new SOL25Object($scope->getClass('String'), 'nil');
            case 'isNumber':
                return $this->boolResult(false, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(false, $scope);
            case 'isNil':
                return $this->boolResult(true, $scope);
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
