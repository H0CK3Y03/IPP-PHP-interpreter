<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Literal.php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\SOL25Object;
use IPP\Student\Exception;
use IPP\Student\Scope;

/**
 * Represents a literal value (Integer, String, Boolean, Nil, or Class reference).
 */
class Literal
{
    /**
     * Type of the literal (e.g., Integer, String, True, False, Nil, class).
     *
     * @var string
     */
    public string $type;

    /**
     * Value of the literal.
     *
     * @var string|int
     */
    public string|int $value;

    /**
     * Literal constructor.
     *
     * @param string $type Type of the literal.
     * @param string|int $value Value associated with the literal.
     */
    public function __construct(string $type, string|int $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Evaluates the literal into a SOL25Object within the given scope.
     *
     * @param Scope $scope
     * @return SOL25Object
     *
     * @throws Exception if the literal type is not recognized.
     */
    public function evaluate(Scope $scope): SOL25Object
    {
        return match ($this->type) {
            'Integer' => new SOL25Object($scope->fetchClass('Integer'), (int) $this->value),
            'String'  => new SOL25Object($scope->fetchClass('String'), (string) $this->value),
            'True'    => $scope->fetchSingleton('true'),
            'False'   => $scope->fetchSingleton('false'),
            'Nil'     => $scope->fetchSingleton('nil'),
            'class'   => new SOL25Object($scope->fetchClass((string) $this->value), null),
            default   => throw new Exception('Literal not found', ReturnCode::INTERPRET_TYPE_ERROR),
        };
    }
}
