<?php

namespace IPP\Student;

use IPP\Student\Scope;
use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;

class SOL25Class
{
    public string $name;
    public ?SOL25Class $parent;

    /** @var array<string> */
    public array $BuiltInMethods = [];

    /** @var array<string, SOL25Method> */
    public array $userDefinedMethods = [];

    public function __construct(string $name, ?SOL25Class $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    // Add a built-in method to the class
    public function addBuiltInMethod(string $selector): void
    {
        $this->BuiltInMethods[] = $selector;
    }

    // Add a user-defined method to the class
    public function addMethod(SOL25Method $method): void
    {
        $this->userDefinedMethods[$method->name] = $method;
    }

    /**
     * Get the method corresponding to a selector name, checking both user-defined and built-in methods
     * @return array{type: string, method: mixed, class: SOL25Class}|null
     */
    public function getMethod(string $selector): ?array
    {
        if (isset($this->userDefinedMethods[$selector])) {
            return ['type' => 'user', 'method' => $this->userDefinedMethods[$selector], 'class' => $this];
        }
        if (in_array($selector, $this->BuiltInMethods)) {
            return ['type' => 'builtin', 'method' => $selector, 'class' => $this];
        }
        return $this->parent?->getMethod($selector);
    }

    // Check if the current class is a subclass or the same as another class
    public function isSubclassOrSame(SOL25Class $diff): bool
    {
        $current = $this;
        while ($current !== null) {
            if ($current === $diff) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Switch the method based on the selector name for the receiver object.
     * @param SOL25Object $receiverObj The receiver object that the method is invoked on.
     * @param string $selectorName The method name or selector.
     * @param Scope $scope The current scope in which the method is evaluated.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj The sender objects.
     * @return SOL25Object The result of the method call.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        switch ($selectorName) {
            case 'whileTrue':
                // Assuming a specific behavior for 'whileTrue'
                return new SOL25Object($scope->getClass('String'), 'nil');
            case 'isNumber':
                return $this->boolResult(false, $scope); // Assuming 'false' for this case
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(true, $scope);
            case 'isNil':
                return $this->boolResult(false, $scope);
            default:
                return $scope->getSingleton('nil'); // Default: return the 'nil' singleton
        }
    }

    /**
     * Helper method to return a SOL25Object with a boolean value.
     * 
     * @param bool $value The boolean value to wrap.
     * @param Scope $scope The scope in which the result is evaluated.
     * @return SOL25Object The boolean result wrapped in a SOL25Object.
     */
    private function boolResult(bool $value, Scope $scope): SOL25Object
    {
        return new SOL25Object($scope->getClass('Boolean'), $value ? 'true' : 'false');
    }
}
