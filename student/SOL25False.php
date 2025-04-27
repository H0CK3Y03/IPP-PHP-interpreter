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

class SOL25False extends SOL25ObjectClass
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
                return $scope->getSingleton('false');
            case 'identicalTo:':
                return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);
            case 'asString':
                return new SOL25Object($scope->getClass('String'), '');
            case 'isNumber':
                return $scope->getSingleton('false');
            case 'isString':
                return $scope->getSingleton('false');
            case 'isBlock':
                return $scope->getSingleton('false');
            case 'isNil':
                return $scope->getSingleton('false');
            case 'not':
                return $scope->getSingleton('true');
            case 'and:':
                return $scope->getSingleton('false');
            case 'or:':
                return $senderObj[0]->evaluate($scope);
            case 'ifTrue:ifFalse:':
                if ($receiverObj->class instanceof SolTrueClass) {
                    return $senderObj[0]->evaluate($scope);
                }
                if ($receiverObj->class instanceof SOL25False) {
                    return $senderObj[1]->evaluate($scope);
                }
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
