<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;
use IPP\Student\SOL25Class;
use IPP\Student\SOL25Object;
use IPP\Student\Exception;
use IPP\Student\Scope;

class SOL25ObjectClass extends SOL25Class
{
    /**
     * Handles method calls based on selector names and returns the appropriate SOL25Object.
     *
     * @param SOL25Object $receiverObj The receiver object for the method call.
     * @param string $selectorName The name of the selector (method or attribute).
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the method call.
     * @throws Exception If the method is not found.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        // Get the value of the receiver's '__value__' attribute.
        /** @var string|int|bool $receiverVal */
        $receiverVal = $receiverObj->getAttr('__value__');

        // Switch based on the selector name.
        switch ($selectorName) {
            case 'identicalTo:':
                return $this->evaluateIdenticalTo($receiverObj, $senderObj, $scope);
            case 'equalTo:':
                return $this->evaluateEqualTo($receiverObj, $senderObj, $scope);
            case 'asString':
                return $this->convertToString($scope);
            case 'isNumber':
            case 'isString':
            case 'isBlock':
            case 'isNil':
                return $this->boolResult(false, $scope); // All these cases return false
            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * Compares the class of the receiver object with the sender object.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * @param Scope $scope The current scope.
     *
     * @return SOL25Object The result of the comparison (true or false).
     */
    private function evaluateIdenticalTo(SOL25Object $receiverObj, ?array $senderObj, Scope $scope): SOL25Object
    {
        $senderClass = $senderObj[0]->evaluate($scope)->class;
        return $this->boolResult($receiverObj->class === $senderClass, $scope);
    }

    /**
     * Compares the value of the receiver object with the value of the sender object.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * @param Scope $scope The current scope.
     *
     * @return SOL25Object The result of the comparison (true or false).
     */
    private function evaluateEqualTo(SOL25Object $receiverObj, ?array $senderObj, Scope $scope): SOL25Object
    {
        $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
        return $this->boolResult($receiverObj->getAttr('__value__') == $senderVal, $scope);
    }

    /**
     * Converts the receiver object to a String representation.
     *
     * @param Scope $scope The current scope.
     *
     * @return SOL25Object A new SOL25Object representing the String.
     */
    private function convertToString(Scope $scope): SOL25Object
    {
        return new SOL25Object($scope->getClass('String'), '');
    }

    /**
     * Returns a boolean result object based on the input value.
     *
     * @param bool $val The boolean value to convert to a SOL25Object.
     * @param Scope $scope The current execution scope.
     *
     * @return SOL25Object A SOL25Object representing the boolean value.
     */
    public function boolResult(bool $val, Scope $scope): SOL25Object
    {
        return $scope->getSingleton($val ? 'true' : 'false');
    }
}
