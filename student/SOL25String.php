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

class SOL25String extends SOL25ObjectClass
{
    /**
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @return SOL25Object
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        $receiverVal = $receiverObj->getAttr('__value__');
        switch ($selectorName) {
            case 'new':
                return new SOL25Object($scope->getClass($this->name), '');
            case 'isNumber':
                return $scope->getSingleton('false');
            case 'isString':
                return $scope->getSingleton('true');
            case 'isBlock':
                return $scope->getSingleton('false');
            case 'isNil':
                return $scope->getSingleton('false');
            case 'identicalTo:':
                return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);
            case 'read':
                return new SOL25Object($scope->getClass('String'), (string) $scope->input);
            case 'print':
                $scope->stdout->writeString(stripcslashes($receiverVal));
                return $receiverObj;
            case 'asInteger':
                if (filter_var($receiverVal, FILTER_VALIDATE_INT) !== false) {
                    return new SOL25Object($scope->getClass('Integer'), (int) $receiverVal);
                } else {
                    return $scope->getSingleton('nil');
                }
            case 'asString':
                return $receiverObj;
            case 'concatenateWith:':
                if ($senderObj[0]->evaluate($scope)->class == $receiverObj->class) {
                    $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                    return new SOL25Object($scope->getClass('String'), $receiverVal.$senderVal);
                } else {
                    return $scope->getSingleton('nil');
                }

            case 'startsWith:endsBefore:':
                $startIdx = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                $endIdx = $senderObj[1]->evaluate($scope)->getAttr('__value__');

                if (($startIdx <= 0) || ($endIdx <= 0)) {
                    return $scope->getSingleton('nil');
                }
                if ($endIdx - $startIdx <= 0) {
                    return new SOL25Object($scope->getClass('String'), '');
                }

                return new SOL25Object($scope->getClass('String'), substr($receiverVal, $startIdx - 1, $endIdx - $startIdx));
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
