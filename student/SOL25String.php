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
     * Handles method calls based on the selector name for a String object.
     *
     * @param SOL25Object $receiverObj The receiver object for the method call.
     * @param string $selectorName The name of the selector (method or attribute).
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object|null> $senderObj
     * 
     * @return SOL25Object The result of the method call.
     * @throws Exception If the method is not found.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        $receiverVal = $receiverObj->getAttr('__value__');
        switch ($selectorName) {
            case 'new':
                return $this->createNewStringObject($scope);
            case 'isNumber':
            case 'isBlock':
            case 'isNil':
                return $this->boolResult(false, $scope);
            case 'isString':
                return $this->boolResult(true, $scope);
            case 'identicalTo:':
                return $this->evaluateIdenticalTo($receiverObj, $senderObj, $scope);
            case 'equalTo:':
                return $this->evaluateEqualTo($receiverObj, $senderObj, $scope);
            case 'read':
                return $this->readFromInput($scope);
            case 'print':
                $scope->stdout->writeString(stripcslashes($receiverVal));
                return $receiverObj;
            case 'asInteger':
                return $this->convertToInteger($receiverVal, $scope);
            case 'asString':
                return $receiverObj;
            case 'concatenateWith:':
                return $this->concatenateStrings($receiverObj, $senderObj, $scope);
            case 'startsWith:endsBefore:':
                return $this->substringBetweenIndexes($receiverObj, $senderObj, $scope);
            default:
                throw new Exception('Unknown method', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
    /**
     * Creates a new String object with an empty value.
     *
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The new String object.
     */
    private function createNewStringObject(Scope $scope): SOL25Object
    {
        return new SOL25Object($scope->fetchClass($this->name), '');
    }
    /**
     * Compares the receiver object to the sender object for identity.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @param Scope $scope The current scope.
     * 
     * @return SOL25Object The result of the comparison (true or false).
     * 
     */
    private function evaluateIdenticalTo(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        return $this->boolResult($receiverObj->class === $senderObj[0]->evaluate($scope)->class, $scope);
    }
    /**
     * Compares the receiver string value to the sender string value for equality.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @param Scope $scope The current scope.
     * 
     * @return SOL25Object The result of the equality check (true or false).
     */
    private function evaluateEqualTo(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');
        return $this->boolResult($receiverObj->getAttr('__value__') == $senderVal, $scope);
    }
    /**
     * Reads input and returns it as a String object.
     *
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The String object representing the input.
     */
    private function readFromInput(Scope $scope): SOL25Object
    {
        return new SOL25Object($scope->fetchClass('String'), (string) $scope->input);
    }
    /**
     * Converts the string value to an Integer, or returns Nil if it is not a valid integer.
     *
     * @param string $value The string value to convert.
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The Integer object or Nil.
     */
    private function convertToInteger(string $value, Scope $scope): SOL25Object
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return new SOL25Object($scope->fetchClass('Integer'), (int) $value);
        } else {
            return $scope->fetchSingleton('nil');
        }
    }
    /**
     * Concatenates the receiver string with another string.
     *
     * @param SOL25Object $receiverObj The receiver object (String).
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The concatenated String object, or Nil if the types do not match.
     */
    private function concatenateStrings(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        $senderVal = $senderObj[0]->evaluate($scope)->getAttr('__value__');

        if ($senderObj[0]->evaluate($scope)->class == $receiverObj->class) {
            return new SOL25Object($scope->fetchClass('String'), $receiverObj->getAttr('__value__') . $senderVal);
        } else {
            return $scope->fetchSingleton('nil');
        }
    }
    /**
     * Extracts a substring from the receiver string, based on the start and end indexes.
     *
     * @param SOL25Object $receiverObj The receiver string object.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The substring as a String object, or Nil if invalid indexes are provided.
     */
    private function substringBetweenIndexes(SOL25Object $receiverObj, array $senderObj, Scope $scope): SOL25Object
    {
        $startIdx = $senderObj[0]->evaluate($scope)->getAttr('__value__');
        $endIdx = $senderObj[1]->evaluate($scope)->getAttr('__value__');

        if ($startIdx <= 0 || $endIdx <= 0) {
            return $scope->fetchSingleton('nil');
        }

        if ($endIdx - $startIdx <= 0) {
            return new SOL25Object($scope->fetchClass('String'), '');
        }

        return new SOL25Object($scope->fetchClass('String'), substr($receiverObj->getAttr('__value__'), $startIdx - 1, $endIdx - $startIdx));
    }
}
