<?php

namespace IPP\Student;

use IPP\Student\SolClass;
use IPP\Student\SolObject;
use IPP\Student\Scopes;

class AstVariable
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function evaluate(Scopes $scope): SolClass|SolObject|null
    {
        if ($this->name === 'self') {
            return $scope->getSelf();
        }

        if ($this->name === 'super') {
            return $scope->getSuper();
        }

        // Check if the first character is uppercase
        if (ctype_upper(substr($this->name, 0, 1))) {
            return $scope->getClass($this->name);
        }
        return $scope->getVar($this->name);
    }
}
