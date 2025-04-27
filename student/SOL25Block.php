<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;
use IPP\Student\SOL25Class;
use IPP\Student\SOL25ObjectClass;
use IPP\Student\Exception;
use IPP\Student\Scope;

class SOL25Block extends SOL25ObjectClass
{
    /**
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @return SOL25Object
     */
    // Are the parameters correct?
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        switch ($selectorName) {
            case 'whileTrue':
                return new SOL25Object($scope->getClass('String'), 'nil');
            case 'isNumber':
                return $this->boolResult(false, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(true, $scope);
            case 'isNil':
                return $this->boolResult(false, $scope);
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
