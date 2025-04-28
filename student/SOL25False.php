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
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
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
                // Compare the class for identicality
                return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                // Compare the __value__ attribute for equality
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
                // Short-circuit logic for 'and'
                return $receiverVal ? $senderObj[0]->evaluate($scope) : $scope->getSingleton('false');
            case 'or:':
                // Short-circuit logic for 'or'
                return $receiverVal ? $scope->getSingleton('true') : $senderObj[0]->evaluate($scope);
            case 'ifTrue:ifFalse:':
                if ($receiverObj->class instanceof SOL25True) {
                    return $senderObj[0]->evaluate($scope);
                }
                if ($receiverObj->class instanceof SOL25False) {
                    return $senderObj[1]->evaluate($scope);
                }
                // If neither case matches, throw an exception
                return $scope->getSingleton('false');
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
