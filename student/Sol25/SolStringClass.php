<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\Sol25;

use IPP\Core\ReturnCode;
use IPP\Student\AstTree\AstBlock;
use IPP\Student\AstTree\AstLiteral;
use IPP\Student\AstTree\AstMessage;
use IPP\Student\AstTree\AstMethod;
use IPP\Student\AstTree\AstVariable;
use IPP\Student\Sol25\SolObjectClass;
use IPP\Student\InterDException;
use IPP\Student\Scopes;

class SolStringClass extends SolObjectClass
{
    /**
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|SolObject> $senderObjects
     * @return SolObject
     */
    public function switchMethod(SolObject $receiverObject, string $selectorName, Scopes $scope, ?array $senderObjects): SolObject
    {
        $receiverValue = $receiverObject->getAttr('__value__');
        switch ($selectorName) {
            case 'new':
                return new SolObject($scope->getClass($this->name), '');
            case 'isNumber':
                return $scope->getSingleton('false');
            case 'isString':
                return $scope->getSingleton('true');
            case 'isBlock':
                return $scope->getSingleton('false');
            case 'isNil':
                return $scope->getSingleton('false');
            case 'identicalTo:':
                return $this->boolResult($receiverObject->class === $senderObjects[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverValue == $senderValue, $scope);
            case 'read':
                return new SolObject($scope->getClass('String'), (string) $scope->input);
            case 'print':
                $scope->stdout->writeString(stripcslashes($receiverValue));
                return $receiverObject;
            case 'asInteger':
                if (filter_var($receiverValue, FILTER_VALIDATE_INT) !== false) {
                    return new SolObject($scope->getClass('Integer'), (int) $receiverValue);
                } else {
                    return $scope->getSingleton('nil');
                }
            case 'asString':
                return $receiverObject;
            case 'concatenateWith:':
                if ($senderObjects[0]->evaluate($scope)->class == $receiverObject->class) {
                    $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                    return new SolObject($scope->getClass('String'), $receiverValue . $senderValue);
                } else {
                    return $scope->getSingleton('nil');
                }

            case 'startsWith:endsBefore:':
                $startIndex = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                $endIndex = $senderObjects[1]->evaluate($scope)->getAttr('__value__');

                if (($startIndex <= 0) || ($endIndex <= 0)) {
                    return $scope->getSingleton('nil');
                }
                if ($endIndex - $startIndex <= 0) {
                    return new SolObject($scope->getClass('String'), '');
                }

                return new SolObject($scope->getClass('String'), substr($receiverValue, $startIndex - 1, $endIndex - $startIndex));
            default:
                throw new InterDException('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
