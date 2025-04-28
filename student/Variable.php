<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Variable.php

namespace IPP\Student;

use IPP\Student\SOL25Class;
use IPP\Student\SOL25Object;
use IPP\Student\Scope;

/**
 * Represents a variable access inside the program.
 */
class Variable
{
    /**
     * The name of the variable being accessed.
     *
     * @var string
     */
    public string $name;

    /**
     * Variable constructor.
     *
     * @param string $name Name of the variable.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Evaluates the variable within the given scope.
     *
     * Handles special cases for 'self', 'super', and class references (capitalized names).
     *
     * @param Scope $scope
     * @return SOL25Class|SOL25Object|null
     */
    public function evaluate(Scope $scope): SOL25Class|SOL25Object|null
    {
        return match (true) {
            $this->name === 'self' => $scope->getSelf(),
            $this->name === 'super' => $scope->getSuper(),
            ctype_upper($this->name[0]) => $scope->fetchClass($this->name),
            default => $scope->fetchVariable($this->name),
        };
    }
}
