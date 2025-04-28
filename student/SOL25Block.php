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
     * Switches method calls based on the selectorName.
     * 
     * @param SOL25Object $receiverObj The object receiving the method call.
     * @param string $selectorName The name of the method being called.
     * @param Scope $scope The scope in which the method is being executed.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj The objects involved in the method call, can be null.
     * @return SOL25Object The result of the method call.
     * @throws Exception If the method is not found.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        switch ($selectorName) {
            case 'whileTrue':
                // Assuming 'whileTrue' always returns a nil String
                return new SOL25Object($scope->getClass('String'), 'nil');
            case 'isBlock':
                return $this->boolResult(true, $scope);
            case 'isNumber':
            case 'isString':
            case 'isNil':
                return $this->boolResult(false, $scope);
            // Default case for unsupported method calls
            default:
                throw new Exception('Method not found: ' . $selectorName, ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
