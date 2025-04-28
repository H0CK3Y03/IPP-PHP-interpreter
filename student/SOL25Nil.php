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
     * Handles method dispatch based on the given selector name.
     *
     * @param SOL25Object $receiverObj The receiver object for the method call.
     * @param string $selectorName The name of the method to invoke.
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the method call.
     * @throws Exception If an unknown method is called.
     */
    public function switchMethod(
        SOL25Object $receiverObj,
        string $selectorName,
        Scope $scope,
        ?array $senderObj
    ): SOL25Object {
        // Retrieve the value attribute of the receiver object
        $receiverValue = $receiverObj->getAttr('__value__');

        // Dispatch method based on the selector name
        switch ($selectorName) {
            case 'new':
                // Return the singleton 'nil' from the scope
                return $scope->fetchSingleton('nil');

            case 'identicalTo:':
                // Compare classes for identity
                return $this->compareClasses($receiverObj, $senderObj, $scope);

            case 'equalTo:':
                // Compare values for equality
                return $this->compareValues($receiverValue, $senderObj, $scope);

            case 'asString':
                // Return a 'String' object with 'nil'
                return new SOL25Object($scope->fetchClass('String'), 'nil');

            case 'isNumber':
            case 'isString':
            case 'isBlock':
                // 'nil' is not a number, string, or block
                return $this->boolResult(false, $scope);

            case 'isNil':
                // 'nil' is nil
                return $this->boolResult(true, $scope);

            default:
                // Throw exception if method not found
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * Compares the class of the receiver object with the sender object.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The boolean result of the comparison.
     */
    private function compareClasses(SOL25Object $receiverObj, ?array $senderObj, Scope $scope): SOL25Object
    {
        $senderClass = $senderObj[0]->evaluate($scope)->class;
        return $this->boolResult($receiverObj->class === $senderClass, $scope);
    }

    /**
     * Compares the value of the receiver object with the value of the sender object.
     *
     * @param mixed $receiverValue The value of the receiver object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The boolean result of the comparison.
     */
    private function compareValues($receiverValue, ?array $senderObj, Scope $scope): SOL25Object
    {
        $senderValue = $senderObj[0]->evaluate($scope)->getAttr('__value__');
        return $this->boolResult($receiverValue == $senderValue, $scope);
    }
}
