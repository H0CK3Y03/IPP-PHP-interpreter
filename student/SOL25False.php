<?php

// Author: Adam Veselý
// Login: xvesela00
// File: SOL25False.php

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
     * Switch method based on the selector name and perform appropriate actions.
     *
     * @param SOL25Object $receiverObj The receiver object on which the method is invoked.
     * @param string $selectorName The method name (selector) to be executed.
     * @param Scope $scope The scope in which the evaluation is performed.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj The sender objects that are passed as arguments.
     * @return SOL25Object The result of the method invocation.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        /** @var string|int|bool $receiverVal */
        $receiverVal = $receiverObj->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return $scope->fetchSingleton('false');

            case 'identicalTo:':
                return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);

            case 'equalTo:':
                $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);

            case 'asString':
                return new SOL25Object($scope->fetchClass('String'), '');

            case 'isNumber':
            case 'isString':
            case 'isBlock':
            case 'isNil':
                return $scope->fetchSingleton('false');

            case 'not':
                return $scope->fetchSingleton('true');

            case 'and:':
                return $receiverVal ? $senderObj[0]->evaluate($scope) : $scope->fetchSingleton('false');

            case 'or:':
                return $receiverVal ? $scope->fetchSingleton('true') : $senderObj[0]->evaluate($scope);

            case 'ifTrue:ifFalse:':
                return $this->evaluateIfTrueFalse($receiverObj, $senderObj, $scope);

            default:
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * Helper method to evaluate the 'ifTrue:ifFalse:' method.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj The sender objects.
     * @param Scope $scope The current scope.
     * @return SOL25Object The result of the evaluation.
     */
    private function evaluateIfTrueFalse(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        if ($receiverObj->class instanceof SOL25True) {
            return $senderObj[0]->evaluate($scope);
        }

        if ($receiverObj->class instanceof SOL25False) {
            return $senderObj[1]->evaluate($scope);
        }

        return $scope->fetchSingleton('false');
    }

    /**
     * Helper method to return a SOL25Object with a boolean result.
     *
     * @param bool $value The boolean value.
     * @param Scope $scope The scope in which the result is evaluated.
     * @return SOL25Object The result wrapped in a SOL25Object.
     */
    public function boolResult(bool $value, Scope $scope): SOL25Object
    {
        return new SOL25Object($scope->fetchClass('Boolean'), $value ? 'true' : 'false');
    }
}
