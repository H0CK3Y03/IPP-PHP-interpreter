<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\AstBlock;
use IPP\Student\AstLiteral;
use IPP\Student\AstMessage;
use IPP\Student\AstMethod;
use IPP\Student\AstVariable;
use IPP\Student\SolObjectClass;
use IPP\Student\InterDException;
use IPP\Student\Scopes;

class SolFalseClass extends SolObjectClass
{
    /**
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|SolObject> $senderObjects
     * @return SolObject
     */
    public function switchMethod(SolObject $receiverObject, string $selectorName, Scopes $scope, ?array $senderObjects): SolObject
    {
        /** @var string|int|bool $receiverValue */
        $receiverValue = $receiverObject->getAttr('__value__');

        switch ($selectorName) {
            case 'new':
                return $scope->getSingleton('false');
            case 'identicalTo:':
                return $this->boolResult($receiverObject->class === $senderObjects[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverValue == $senderValue, $scope);
            case 'asString':
                return new SolObject($scope->getClass('String'), '');
            case 'isNumber':
                return $scope->getSingleton('false');
            case 'isString':
                return $scope->getSingleton('false');
            case 'isBlock':
                return $scope->getSingleton('false');
            case 'isNil':
                return $scope->getSingleton('false');
            case 'not':
                return $scope->getSingleton('true');
            case 'and:':
                return $scope->getSingleton('false');
            case 'or:':
                return $senderObjects[0]->evaluate($scope);
            case 'ifTrue:ifFalse:':
                if ($receiverObject->class instanceof SolTrueClass) {
                    return $senderObjects[0]->evaluate($scope);
                }
                if ($receiverObject->class instanceof SolFalseClass) {
                    return $senderObjects[1]->evaluate($scope);
                }
            default:
                throw new InterDException('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
