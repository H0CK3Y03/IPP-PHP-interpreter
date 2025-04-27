<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\AstBlock;
use IPP\Student\AstLiteral;
use IPP\Student\AstMessage;
use IPP\Student\AstMethod;
use IPP\Student\AstVariable;
use IPP\Student\SolClass;
use IPP\Student\SolObjectClass;
use IPP\Student\InterDException;
use IPP\Student\Scopes;

class SolBlockClass extends SolObjectClass
{
    /**
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|SolObject> $senderObjects
     * @return SolObject
     */
    public function switchMethod(SolObject $receiverObject, string $selectorName, Scopes $scope, ?array $senderObjects): SolObject
    {
        switch ($selectorName) {
            case 'whileTrue':
                return new SolObject($scope->getClass('String'), 'nil');
            case 'isNumber':
                return $this->boolResult(false, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(true, $scope);
            case 'isNil':
                return $this->boolResult(false, $scope);
            default:
                throw new InterDException('Method not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
