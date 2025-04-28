<?php

namespace IPP\Student;

use IPP\Student\Scope;
use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;

/**
 * Represents a class in the SOL25 language.
 */
class SOL25Class
{
    /** @var string The name of the class */
    public string $name;

    /** @var SOL25Class|null The parent class */
    public ?SOL25Class $parent;

    /** @var array<string> The built-in methods for the class */
    public array $builtInMethods = [];

    /** @var array<string, SOL25Method> The user-defined methods of the class */
    public array $userDefinedMethods = [];

    /**
     * SOL25Class constructor.
     * Initializes the class with a name and an optional parent class.
     *
     * @param string $name The name of the class.
     * @param SOL25Class|null $parent The parent class (optional).
     */
    public function __construct(string $name, ?SOL25Class $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    /**
     * Add a built-in method to the class.
     *
     * @param string $selector The name of the built-in method.
     */
    public function addBuiltInMethod(string $selector): void
    {
        $this->builtInMethods[] = $selector;
    }

    /**
     * Add a user-defined method to the class.
     *
     * @param SOL25Method $method The user-defined method to add.
     */
    public function addMethod(SOL25Method $method): void
    {
        $this->userDefinedMethods[$method->name] = $method;
    }

    /**
     * Get a method by its selector, checking both user-defined and built-in methods.
     *
     * @param string $selector The method selector name.
     * @return array{type: string, method: mixed, class: SOL25Class}|null The method information, or null if not found.
     */
    public function getMethod(string $selector): ?array
    {
        if (isset($this->userDefinedMethods[$selector])) {
            return $this->createMethodResponse('user', $this->userDefinedMethods[$selector]);
        }

        if (in_array($selector, $this->builtInMethods)) {
            return $this->createMethodResponse('builtin', $selector);
        }

        return $this->parent?->getMethod($selector);
    }

    /**
     * Create a method response array.
     *
     * @param string $type The type of method (either 'user' or 'builtin').
     * @param mixed $method The method (either a method object or a string for built-in methods).
     * @return array{type: string, method: mixed, class: SOL25Class} The response array.
     */
    private function createMethodResponse(string $type, $method): array
    {
        return ['type' => $type, 'method' => $method, 'class' => $this];
    }

    /**
     * Check if the current class is a subclass or the same as another class.
     *
     * @param SOL25Class $otherClass The class to compare against.
     * @return bool True if the current class is the same or a subclass of the other class, false otherwise.
     */
    public function isSubclassOrSame(SOL25Class $otherClass): bool
    {
        $current = $this;
        while ($current !== null) {
            if ($current === $otherClass) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Switch the method based on the selector name for the receiver object.
     * This method handles the invocation of special methods such as 'whileTrue' and type checks.
     *
     * @param SOL25Object $receiverObj The receiver object on which the method is invoked.
     * @param string $selectorName The method name or selector.
     * @param Scope $scope The current scope in which the method is evaluated.
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj The sender objects.
     * @return SOL25Object The result of the method call.
     */
    public function switchMethod(SOL25Object $receiverObj, string $selectorName, Scope $scope, ?array $senderObj): SOL25Object
    {
        switch ($selectorName) {
            case 'whileTrue':
                return $this->handleWhileTrue($scope);
            case 'isNumber':
                return $this->boolResult(false, $scope);
            case 'isString':
                return $this->boolResult(false, $scope);
            case 'isBlock':
                return $this->boolResult(true, $scope);
            case 'isNil':
                return $this->boolResult(false, $scope);
            default:
                return $this->getNilSingleton($scope);
        }
    }

    /**
     * Handle the 'whileTrue' method, which may involve specific logic.
     *
     * @param Scope $scope The scope in which the method is evaluated.
     * @return SOL25Object The result of the 'whileTrue' method.
     */
    private function handleWhileTrue(Scope $scope): SOL25Object
    {
        // Assuming a specific behavior for 'whileTrue'
        return new SOL25Object($scope->fetchClass('String'), 'nil');
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
        return new SOL25Object($scope->fetchClass('Boolean'), $value ? 'true' : 'false');
    }

    /**
     * Retrieve the 'nil' singleton object.
     *
     * @param Scope $scope The scope in which the singleton is fetched.
     * @return SOL25Object The 'nil' singleton.
     */
    private function getNilSingleton(Scope $scope): SOL25Object
    {
        return $scope->fetchSingleton('nil');
    }
}
