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

class SOL25True extends SOL25ObjectClass
{
    /**
     * Handles method calls for the True singleton object.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param string $selectorName The method selector name.
     * @param Scope $scope The current execution scope.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj Optional method arguments.
     * 
     * @return SOL25Object The result of the method execution.
     * @throws Exception If the selector is not found.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        $receiverVal = $receiverObj->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return $scope->getSingleton('true');

            case 'identicalTo:':
                return $this->evaluateIdenticalTo($receiverObj, $senderObj, $scope);

            case 'equalTo:':
                return $this->evaluateEqualTo($receiverVal, $senderObj, $scope);

            case 'asString':
                return new SOL25Object($scope->getClass('String'), '');

            case 'isNumber':
            case 'isString':
            case 'isBlock':
            case 'isNil':
            case 'not':
                return $scope->getSingleton('false');

            case 'and:':
                return $senderObj[0]->evaluate($scope);

            case 'or:':
                return $scope->getSingleton('true');

            case 'ifTrue:ifFalse:':
                return $this->evaluateIfTrueIfFalse($receiverObj, $senderObj, $scope);

            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * Evaluates 'identicalTo:' method.
     */
    private function evaluateIdenticalTo(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        return $this->boolResult(
            $receiverObj->class === $senderObj[0]->evaluate($scope)->class,
            $scope
        );
    }

    /**
     * Evaluates 'equalTo:' method.
     */
    private function evaluateEqualTo($receiverVal, array $senderObj, Scope $scope): SOL25Object
    {
        $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
        return $this->boolResult($receiverVal == $senderVal, $scope);
    }

    /**
     * Evaluates 'ifTrue:ifFalse:' method based on the receiver's class.
     */
    private function evaluateIfTrueIfFalse(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        if ($receiverObj->class instanceof SOL25True) {
            return $senderObj[0]->evaluate($scope);
        } elseif ($receiverObj->class instanceof SOL25False) {
            return $senderObj[1]->evaluate($scope);
        }

        // Fallback (should not happen if used correctly)
        throw new Exception('Unexpected receiver type in ifTrue:ifFalse:', ReturnCode::INTERPRET_TYPE_ERROR);
    }
}
