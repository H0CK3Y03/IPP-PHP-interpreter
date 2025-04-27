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
use IPP\Student\Sol25\SolClass;
use IPP\Student\Sol25\SolObjectClass;
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
