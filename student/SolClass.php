<?php

namespace IPP\Student;

use IPP\Student\Scopes;
use IPP\Student\AstBlock;
use IPP\Student\AstLiteral;
use IPP\Student\AstMessage;
use IPP\Student\AstMethod;
use IPP\Student\AstVariable;

class SolClass
{
    public string $name;
    public ?SolClass $parent;

    /** @var array<string> */
    public array $BuiltInMethods = [];

    /** @var array<string, SolMethod> */
    public array $userDefinedMethods = [];

    public function __construct(string $name, ?SolClass $parent = null)
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
    public function addMethod(SolMethod $method): void
    {
        $this->userDefinedMethods[$method->name] = $method;
    }

    /**
     * Get the method corresponding to a selector name, checking both user-defined and built-in methods
     * @return array{type: string, method: mixed, class: SolClass}|null
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
    public function isSubclassOrSame(SolClass $other): bool
    {
        $current = $other;
        while ($current !== null) {
            if ($current === $this) {
                return true;
            }
            $current = $other->parent;
        }
        return false;
    }

    /**
     * Switch the method based on the selector name for the receiver object.
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|SolObject> $senderObjects
     * @return SolObject
     */
    public function switchMethod(SolObject $receiverObject, string $selectorName, Scopes $scope, ?array $senderObjects): SolObject
    {
        return $scope->getSingleton('nil');
    }
}
