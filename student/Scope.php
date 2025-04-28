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
    /** @var array<int, array<string, array<string, mixed>>> */
    private array $scopesStack = [];

    /** @var array<string, SOL25Class> */
    private array $classDefinitions = [];

    /** @var array<string, SOL25Object> */
    private array $singletonObjects = [];

    private SOL25Object $self;
    private SOL25Object $super;
    public string $input;
    public StreamWriter $stdout;

    public function __construct(string $input = '')
    {
        $this->input = $input;
        $this->stdout = new StreamWriter(STDOUT);
    }

    /**
     * Initialize a new variable scope.
     * This method pushes an empty scope onto the stack.
     */
    public function startNewScope(): void
    {
        array_push($this->scopesStack, []);
    }

    /**
     * End the current variable scope.
     * If no scope is available, an exception is thrown.
     */
    public function endCurrentScope(): void
    {
        if (empty($this->scopesStack)) {
            throw new Exception('No scope to exit.', ReturnCode::INTERNAL_ERROR);
        }
        array_pop($this->scopesStack);
    }

    /**
     * Add a new variable to the current scope.
     * An exception is thrown if no scope is active.
     */
    public function addVariable(string $varName): void
    {
        $this->ensureScopeIsActive();
        $this->scopesStack[count($this->scopesStack) - 1][$varName] = ['expression' => null];
    }

    /**
     * Assign a value to a variable, searching for it from inner to outer scopes.
     * Throws an exception if the variable cannot be found.
     */
    public function assignVariable(string $varName, SOL25Object $value): void
    {
        $count = count($this->scopesStack);
        while ($count-- > 0) {
            $scope = &$this->scopesStack[$count];
            if (isset($scope[$varName])) {
                $scope[$varName]['expression'] = $value;
                return;
            }
        }

        $this->addVariableInCurrentScope($varName, $value);
    }

    /**
     * Retrieve the value of a variable from the innermost to outermost scope.
     * Throws an exception if the variable cannot be found.
     */
    public function fetchVariable(string $varName): ?SOL25Object
    {
        foreach (array_reverse($this->scopesStack) as $scope) {
            if (isset($scope[$varName])) {
                return $scope[$varName]['expression'];
            }
        }

        throw new Exception("Unknown variable: '$varName'.", ReturnCode::PARSE_UNDEF_ERROR);
    }

    /**
     * Checks whether a variable exists in any of the scopes.
     */
    public function doesVariableExist(string $varName): bool
    {
        foreach (array_reverse($this->scopesStack) as $scope) {
            if (isset($scope[$varName])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Register a class in the scope.
     */
    public function registerClass(string $className, SOL25Class $class): void
    {
        $this->classDefinitions[$className] = $class;
    }

    /**
     * Fetch a class by its name from the registered class definitions.
     * Throws an exception if the class is not registered.
     */
    public function fetchClass(string $className): SOL25Class
    {
        if (!isset($this->classDefinitions[$className])) {
            throw new Exception("Class '$className' not registered.", ReturnCode::INTERPRET_TYPE_ERROR);
        }
        return $this->classDefinitions[$className];
    }

    /**
     * Store a singleton object in the scope.
     */
    public function storeSingleton(string $singletonName, SOL25Object $object): void
    {
        $this->singletonObjects[$singletonName] = $object;
    }

    /**
     * Retrieve a singleton object by its name.
     * Returns null if the singleton doesn't exist.
     */
    public function fetchSingleton(string $singletonName): ?SOL25Object
    {
        return $this->singletonObjects[$singletonName] ?? null;
    }

    /**
     * Set the current object represented by $self.
     */
    public function setSelf(SOL25Object $self): void
    {
        $this->self = $self;
    }

    /**
     * Get the current object represented by $self.
     */
    public function getSelf(): SOL25Object
    {
        return $this->self;
    }

    /**
     * Set the superclass object represented by $super.
     */
    public function setSuper(SOL25Object $super): void
    {
        $this->super = $super;
    }

    /**
     * Get the superclass object represented by $super.
     */
    public function getSuper(): SOL25Object
    {
        return $this->super;
    }

    /**
     * Ensures that a scope is active before proceeding.
     * Throws an exception if no scope is active.
     */
    private function ensureScopeIsActive(): void
    {
        if (empty($this->scopesStack)) {
            throw new Exception('No active scope to add a variable.', ReturnCode::INTERNAL_ERROR);
        }
    }

    /**
     * Adds a variable to the current scope, in case it was not found in any outer scopes.
     * Throws an exception if no scope is active.
     */
    private function addVariableInCurrentScope(string $varName, SOL25Object $value): void
    {
        $this->ensureScopeIsActive();
        $this->scopesStack[count($this->scopesStack) - 1][$varName] = ['expression' => $value];
    }
}
