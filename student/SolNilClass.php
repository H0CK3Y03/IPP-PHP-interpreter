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

class SolNilClass extends SolObjectClass
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
                return $scope->getSingleton('nil');
            case 'identicalTo:':
                return $this->boolResult($receiverObject->class === $senderObjects[0]->evaluate($scope)->class, $scope);
            case 'equalTo:':
                $senderValue = $senderObjects[0]->evaluate($scope)->getAttr('__value__');
                return $this->boolResult($receiverValue == $senderValue, $scope);
            case 'asString':
                return new SolObject($scope->getClass('String'), 'nil');
            case 'isNumber':
                return $this->boolResult(false, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(false, $scope);
            case 'isNil':
                return $this->boolResult(true, $scope);
            default:
                throw new InterDException('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
