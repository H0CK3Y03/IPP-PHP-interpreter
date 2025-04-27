<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SOL25Object;
use IPP\Student\Exception;
use IPP\Student\Scope;

class Literal
{
    public string $type;
    public string|int $val;

    public function __construct(string $type, string|int $val)
    {
        $this->type = $type;
        $this->val = $val;
    }

    public function evaluate(Scope $scope): SOL25Object
    {
        switch ($this->type) {
            case 'Integer':
                return new SOL25Object($scope->getClass('Integer'), (int) $this->val);
            case 'String':
                return new SOL25Object($scope->getClass('String'), (string) $this->val);
            case 'True':
                return $scope->getSingleton('true');
            case 'False':
                return $scope->getSingleton('false');
            case 'Nil':
                return $scope->getSingleton('nil');
            case 'class':
                return new SOL25Object($scope->getClass($this->val), null);
            default:
                throw new Exception('Literal not found', ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
