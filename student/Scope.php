<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use IPP\Core\StreamWriter;
use IPP\Student\SOL25Class;
use IPP\Student\SOL25Object;
use IPP\Student\Exception;

class Scope
{
    public string $input;

    /** @var array<int, array<string, array<string, mixed>>> */
    private array $scopes = [];

    /** @var array<string, SOL25Class> */
    private array $classes = [];

    /** @var array<string, SOL25Object> */
    private array $singletons = [];

    private SOL25Object $self;
    private SOL25Object $super;
    public StreamWriter $stdout;

    public function __construct(string $input = '')
    {
        $this->input = $input;
        $this->stdout = new StreamWriter(STDOUT);
    }

    /**
     * Start a new variable scope.
     * Pushes a new empty scope onto the scope stack.
     */
    public function enterScope(): void
    {
        array_push($this->scopes, []);
    }

    /**
     * End the current variable scope.
     * Pops the top scope from the scope stack.
     * Throws an exception if no scope is available to exit.
     */
    public function exitScope(): void
    {
        if (count($this->scopes) > 0) {
            array_pop($this->scopes);
        } else {
            throw new Exception('No scope to exit.', ReturnCode::INTERNAL_ERROR);
        }
    }

    /**
     * Add a variable to the current scope.
     * Throws an exception if no active scope is available.
     */
    public function addVar(string $varName): void
    {
        if (count($this->scopes) > 0) {
            $this->scopes[count($this->scopes) - 1][$varName] = ['expression' => null];
        } else {
            throw new Exception('No active scope to add a variable to.', ReturnCode::INTERNAL_ERROR);
        }
    }

    /**
     * Set a variable's value in the scope.
     * Searches from the innermost scope to the outer scopes.
     * Throws an exception if the variable is not found.
     */
    public function setVar(string $varName, SOL25Object $expr): void
    {
        $count = count($this->scopes);
        while ($count-- > 0) {
            $scope = &$this->scopes[$count];
            if (array_key_exists($varName, $scope)) {
                $scope[$varName]['expression'] = $expr;
                return;
            }
        }

        // If variable not found, set it in the current scope
        if (count($this->scopes) > 0) {
            $this->scopes[count($this->scopes) - 1][$varName] = ['expression' => $expr];
        } else {
            throw new Exception("Error: Unknown '$varName' variable\n", ReturnCode::PARSE_UNDEF_ERROR);
        }
    }

    /**
     * Get the value of a variable.
     * Searches from the innermost scope to the outer scopes.
     * Throws an exception if the variable is not found.
     */
    public function getVar(string $varName): ?SOL25Object
    {
        $count = count($this->scopes);
        while ($count-- > 0) {
            $scope = $this->scopes[$count];
            if (array_key_exists($varName, $scope)) {
                return $scope[$varName]['expression'];
            }
        }
        throw new Exception("Error: Unknown '$varName' variable\n", ReturnCode::PARSE_UNDEF_ERROR);
    }

    /**
     * Check if a variable exists in the current scope or any outer scope.
     */
    public function hasVar(string $varName): bool
    {
        foreach (array_reverse($this->scopes) as $scope) {
            if (array_key_exists($varName, $scope)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Register a class definition in the scope.
     */
    public function registerClass(string $name, SOL25Class $class): void
    {
        $this->classes[$name] = $class;
    }

    /**
     * Get a class definition by name.
     * Throws an exception if the class is not registered.
     */
    public function getClass(string $name): SOL25Class
    {
        if (!isset($this->classes[$name])) {
            throw new Exception("Class '$name' not registered.", ReturnCode::INTERPRET_TYPE_ERROR);
        }
        return $this->classes[$name];
    }

    /**
     * Set a singleton object in the scope.
     */
    public function setSingleton(string $name, SOL25Object $obj): void
    {
        $this->singletons[$name] = $obj;
    }

    /**
     * Get a singleton object by name.
     * Returns null if the singleton doesn't exist.
     */
    public function getSingleton(string $name): ?SOL25Object
    {
        return $this->singletons[$name] ?? null;
    }

    /**
     * Set the $self object, representing the current object.
     */
    public function setSelf(SOL25Object $obj): void
    {
        $this->self = $obj;
    }

    /**
     * Get the $self object.
     */
    public function getSelf(): SOL25Object
    {
        return $this->self;
    }

    /**
     * Set the $super object, representing the superclass.
     */
    public function setSuper(SOL25Object $obj): void
    {
        $this->super = $obj;
    }

    /**
     * Get the $super object.
     */
    public function getSuper(): SOL25Object
    {
        return $this->super;
    }
}
