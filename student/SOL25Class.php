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
        $current = $diff;
        while ($current !== null) {
            if ($current === $this) {
                return true;
            }
            $current = $diff->parent;
        }
        return false;
    }

    /**
     * Switch the method based on the selector name for the receiver object.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @return SOL25Object
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        return $scope->getSingleton('nil');
    }
}
