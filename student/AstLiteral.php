<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SolObject;
use IPP\Student\InterDException;
use IPP\Student\Scopes;

class AstLiteral
{
    public string $type;
    public string|int $value;

    public function __construct(string $type, string|int $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function evaluate(Scopes $scope): SolObject
    {
        switch ($this->type) {
            case 'Integer':
                return new SolObject($scope->getClass('Integer'), (int) $this->value);
            case 'String':
                return new SolObject($scope->getClass('String'), (string) $this->value);
            case 'True':
                return $scope->getSingleton('true');

            case 'False':
                return $scope->getSingleton('false');

            case 'Nil':
                return $scope->getSingleton('nil');

            case 'class':
                return new SolObject($scope->getClass($this->value), null);
            default:
                throw new InterDException('Literal not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
