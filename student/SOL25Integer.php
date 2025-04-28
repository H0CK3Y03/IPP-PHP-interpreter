<?php

// Author: Adam Veselý
// Login: xvesela00
// File: SOL25Integer.php

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

class SOL25Integer extends SOL25ObjectClass
{
    // Stores the result of the last evaluated operation
    public ?SOL25Object $lastResult = null;

    /**
     * Switches between method invocations based on the method name (selector).
     *
     * @param SOL25Object $receiverObj The object receiving the message.
     * @param string $selectorName The method name to be invoked.
     * @param Scope $scope The current scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObjs
     * @return SOL25Object The result of the method invocation.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObjs): SOL25Object
    {
        // Get the value of the receiver object
        $receiverVal = $receiverObj->getAttr('__value__');

        // Switch statement for different method selectors
        switch ($selectorName) {
            case 'new':
                // Create a new Integer object with value 0
                return $this->createNewIntegerObject($scope, 0);
            case 'isNumber':
                // Return true if it's a number
                return $this->boolResult(true, $scope);
            case 'isString':
            case 'isBlock':
            case 'isNil':
                // Return false for non-number types
                return $this->boolResult(false, $scope);
            case 'plus:':
                // Addition of the receiver value and sender value
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->fetchClass('Integer'), $receiverVal + $senderVal);
            case 'minus:':
                // Subtraction of the receiver value and sender value
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->fetchClass('Integer'), (int) $receiverVal - (int) $senderVal);
            case 'multiplyBy:':
                // Multiplication of the receiver value and sender value
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return new SOL25Object($scope->fetchClass('Integer'), (int) $receiverVal * (int) $senderVal);
            case 'divBy:':
                // Division of the receiver value and sender value, handles division by zero
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return $this->handleDivision($receiverVal, $senderVal, $scope);
            case 'asString':
                // Convert the receiver value to a String object
                return new SOL25Object($scope->fetchClass('String'), (string) $receiverVal);
            case 'asInteger':
                // Return the receiver object as an Integer object
                return $receiverObj;
            case 'identicalTo:':
                // Check if the receiver is identical to the sender object
                return $this->boolResult($receiverObj->class === $senderObjs[0]->evaluate($scope)->class, $scope);
            case 'greaterThan:':
                // Check if the receiver value is greater than the sender value
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal > $senderVal, $scope);
            case 'equalTo:':
                // Check if values are equal (==)
                $senderVal = $senderObjs[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverVal == $senderVal, $scope);
            case 'timesRepeat:':
                // Repeat the given block a number of times as per the receiver value
                return $this->handleTimesRepeat($senderObjs, $scope, $receiverVal);
            default:
                // Throw an exception if the method is not found
                throw new Exception('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * Creates a new Integer object with the given value.
     *
     * @param Scope $scope The current scope.
     * @param int $value The value of the new Integer object.
     * @return SOL25Object The newly created Integer object.
     */
    private function createNewIntegerObject(Scope $scope, int $value): SOL25Object
    {
        return new SOL25Object($scope->fetchClass('Integer'), $value);
    }

    /**
     * Handles division operation and checks for division by zero.
     *
     * @param int $receiverVal The value of the receiver object.
     * @param int $senderVal The value of the sender object.
     * @param Scope $scope The current scope.
     * @return SOL25Object The result of the division.
     * @throws Exception If division by zero occurs.
     */
    private function handleDivision(int $receiverVal, int $senderVal, Scope $scope): SOL25Object
    {
        // Check if division by zero occurs
        if ($senderVal === 0) {
            throw new Exception("Division by zero", ReturnCode::INTERPRET_VALUE_ERROR);
        }

        // Return the result of the division
        return $this->createNewIntegerObject($scope, $receiverVal / $senderVal);
    }

    /**
     * Handles the 'timesRepeat:' method by repeating the block the given number of times.
     *
     *@param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObjs
     * @param Scope $scope The current scope.
     * @param int $receiverVal The number of repetitions.
     * @return SOL25Object The last result or 'nil' if no result exists.
     */
    private function handleTimesRepeat(?array $senderObjs, Scope $scope, int $receiverVal): SOL25Object
    {
        // Repeat the block receiverVal times
        for ($i = 1; $i <= $receiverVal; $i++) {
            // Create an iteration object
            $iter = [new SOL25Object($scope->fetchClass('Integer'), $i)];
            // Evaluate the sender object for each iteration
            $this->lastResult = $this->evaluateSender($senderObjs[0], $scope, $iter);
        }

        // Return the last result or 'nil' if no result exists
        return $this->lastResult ?? $scope->fetchSingleton('nil');
    }

    /**
     * Evaluates the sender object (either a block or a value) and returns the result.
     *
     * @param mixed $senderObj The sender object to evaluate.
     * @param Scope $scope The current scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object> $iter The iteration objects.
     * @return SOL25Object The evaluated result.
     */
    private function evaluateSender($senderObj, Scope $scope, array $iter): SOL25Object
    {
        // If the sender is a Block, evaluate it directly with the iteration
        if ($senderObj instanceof Block) {
            return $senderObj->evaluate($scope, $iter);
        }

        // Otherwise, evaluate the sender object and then execute its method 'value:'
        $senderObj = $senderObj->evaluate($scope);
        return $senderObj->class->getMethod('value:')['method']->block->evaluate($scope, $iter);
    }
}
