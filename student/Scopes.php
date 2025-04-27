<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use IPP\Core\StreamWriter;
use IPP\Student\Sol25\SolClass;
use IPP\Student\Sol25\SolObject;
use IPP\Student\InterDException;

class Scopes
{
    public string $input;

    /** @var array<int, array<string, array<string, mixed>>> */
    private array $scopes = [];

    /** @var array<string, SolClass> */
    private array $classes = [];

    /** @var array<string, SolObject> */
    private array $singletons = [];

    private SolObject $self;
    private SolObject $super;
    public StreamWriter $stdout;

    public function __construct(string $input = '')
    {
        $this->input = $input;
        $this->stdout = new StreamWriter(STDOUT);
    }

    // Start a new variable scope
    public function enterScope(): void
    {
        array_push($this->scopes, []);
    }

    // End the current variable scope
    public function exitScope(): void
    {
        if (count($this->scopes) > 0) {
            array_pop($this->scopes);
        } else {
            throw new InterDException('No scope to exit.', ReturnCode::INTERNAL_ERROR);
        }
    }

    // Add a variable to the current scope
    public function addVar(string $varName): void
    {
        if (count($this->scopes) > 0) {
            $this->scopes[count($this->scopes) - 1][$varName] = ['expression' => null];
        } else {
            throw new InterDException('No active scope to add a variable to.', ReturnCode::INTERNAL_ERROR);
        }
    }

    // Set a variable's value
    public function setVar(string $varName, SolObject $expression): void
    {
        $count = count($this->scopes);
        while ($count-- > 0) {
            $scope = &$this->scopes[$count];
            if (array_key_exists($varName, $scope)) {
                $scope[$varName]['expression'] = $expression;
                return;
            }
        }

        // If not found, set it in the current scope
        if (count($this->scopes) > 0) {
            $this->scopes[count($this->scopes) - 1][$varName] = ['expression' => $expression];
        } else {
            throw new InterDException("Error: Unknown '$varName' variable\n", ReturnCode::PARSE_UNDEF_ERROR);
        }
    }

    // Get the value of a variable
    public function getVar(string $varName): ?SolObject
    {
        $count = count($this->scopes);
        while ($count-- > 0) {
            $scope = $this->scopes[$count];
            if (array_key_exists($varName, $scope)) {
                return $scope[$varName]['expression'];
            }
        }
        throw new InterDException("Error: Unknown '$varName' variable\n", ReturnCode::PARSE_UNDEF_ERROR);
    }

    // Check if a variable exists
    public function hasVar(string $varName): bool
    {
        foreach (array_reverse($this->scopes) as $scope) {
            if (array_key_exists($varName, $scope)) {
                return true;
            }
        }
        return false;
    }

    // Add a class definition
    public function registerClass(string $name, SolClass $class): void
    {
        $this->classes[$name] = $class;
    }

    // Get a class by name
    public function getClass(string $name): SolClass
    {
        if (!isset($this->classes[$name])) {
            throw new InterDException("Class '$name' not registered.", ReturnCode::INTERPRET_TYPE_ERROR);
        }
        return $this->classes[$name];
    }

    // Add a singleton object
    public function setSingleton(string $name, SolObject $object): void
    {
        $this->singletons[$name] = $object;
    }

    // Get a singleton object
    public function getSingleton(string $name): ?SolObject
    {
        return $this->singletons[$name] ?? null;
    }

    // Set the $self object
    public function setSelf(SolObject $object): void
    {
        $this->self = $object;
    }

    // Get the $self object
    public function getSelf(): SolObject
    {
        return $this->self;
    }

    // Set the $super object
    public function setSuper(SolObject $object): void
    {
        $this->super = $object;
    }

    // Get the $super object
    public function getSuper(): SolObject
    {
        return $this->super;
    }
}
