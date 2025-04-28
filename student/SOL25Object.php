<?php

namespace IPP\Student;

use IPP\Student\Block;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Variable;
use IPP\Student\Scope;
use IPP\Student\Exception;
use IPP\Core\ReturnCode;

class SOL25Object
{
    public SOL25Class $class;
    /**
     * @var array<string, mixed>
     */
    public array $attr = [];

    /**
     * Constructor for the SOL25Object class
     * 
     * @param SOL25Class $class The class of the object.
     * @param mixed $val The value to set for the object.
     */
    public function __construct(SOL25Class $class, mixed $val = null)
    {
        $this->class = $class;
        $this->setAttr('__value__', $val);
    }

    /**
     * Sets an attribute's name and value.
     * 
     * @param string $name The name of the attribute.
     * @param mixed $val The value of the attribute.
     */
    public function setAttr(string $name, mixed $val): void
    {
        $this->attr[$name] = $val;
    }

    /**
     * Gets the value of an attribute by its name.
     * 
     * @param string $name The name of the attribute.
     * 
     * @return mixed The value of the attribute, or null if not found.
     */
    public function getAttr(string $name): mixed
    {
        return $this->attr[$name] ?? null;
    }

    /**
     * Sends a message to another object based on the selector.
     * 
     * @param SOL25Object $receiverObj The receiver object for the message.
     * @param string $selector The selector (method name or attribute).
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the message handling.
     * @throws Exception If the method or attribute is not found.
     */
    public function sendMessage(SOL25Object $receiverObj, string $selector, Scope $scope, ?array $senderObj): SOL25Object
    {
        // Attempt to get the method information for the selector
        $methodInfo = $this->class->getMethod($selector);

        // If no method is found, handle the case
        if (!$methodInfo) {
            return $this->handleNoMethodFound($receiverObj, $selector, $scope, $senderObj);
        }

        // Delegate to method type handler (user-defined or built-in)
        return $this->handleMethodType($methodInfo, $receiverObj, $scope, $senderObj);
    }

    /**
     * Handles the case where no method is found for the given selector.
     * 
     * @param SOL25Object $receiverObj The receiver object.
     * @param string $selector The method or attribute selector.
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the message handling.
     * @throws Exception If the attribute is not found.
     */
    private function handleNoMethodFound(SOL25Object $receiverObj, string $selector, Scope $scope, ?array $senderObj): SOL25Object
    {
        // Handle 'value' and 'value:' selectors for SOL25Block
        if ($this->isBlockValueSelector($selector)) {
            return $this->evaluateBlockValue($receiverObj, $scope, $senderObj);
        }

        // Handle 'from:' selector for creating a new SOL25Object
        if ($this->isFromSelector($selector)) {
            return $this->createFromSenderValue($scope, $senderObj);
        }

        // Handle 'new' selector to return a new instance of the class
        if ($selector === 'new') {
            return $this->handleNewSelector($scope);
        }

        // Handle attribute setting with 'selector:' format
        if ($this->isAttributeSetter($selector)) {
            return $this->setAttributeFromSender($selector, $senderObj, $scope);
        }

        // Return attribute value or throw error if not found
        return $this->getAttributeOrThrow($selector);
    }

    /**
     * Determines if the selector is for 'value' or 'value:' for blocks.
     *
     * @param string $selector The selector to check.
     * 
     * @return bool Whether the selector is related to block values.
     */
    private function isBlockValueSelector(string $selector): bool
    {
        return ($selector === 'value' || str_starts_with($selector, 'value:')) && $this->class instanceof SOL25Block;
    }

    /**
     * Evaluates the block's value based on the sender objects.
     *
     * @param SOL25Object $receiverObj The receiver object.
     * @param Scope $scope The current scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of evaluating the block's value.
     */
    private function evaluateBlockValue(SOL25Object $receiverObj, Scope $scope, ?array $senderObj): SOL25Object
    {
        return $receiverObj->getAttr('__value__')->evaluate($scope, $senderObj);
    }

    /**
     * Checks if the selector is a 'from:' selector.
     *
     * @param string $selector The selector to check.
     * 
     * @return bool Whether the selector is 'from:'.
     */
    private function isFromSelector(string $selector): bool
    {
        return str_starts_with($selector, 'from:');
    }

    /**
     * Handles the 'new' selector to create a new SOL25Object based on the class name.
     * 
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object A new instance of the class.
     */
    private function handleNewSelector(Scope $scope): SOL25Object
    {
        switch ($this->class->name) {
            case 'False':
                return $scope->fetchSingleton('false');
            case 'True':
                return $scope->fetchSingleton('true');
            case 'Nil':
                return $scope->fetchSingleton('nil');
            case 'String':
                return new SOL25Object($scope->fetchClass('String'), '');
            case 'Integer':
                return new SOL25Object($scope->fetchClass('Integer'), 0);
            default:
                return new SOL25Object($scope->fetchClass($this->class->name), null);
        }
    }

    /**
     * Creates a new SOL25Object from the sender value.
     *
     * @param Scope $scope The current scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object A new SOL25Object based on the sender's value.
     */
    private function createFromSenderValue(Scope $scope, ?array $senderObj): SOL25Object
    {
        return new SOL25Object(
            $scope->fetchClass($this->class->name),
            $senderObj[0]->evaluate($scope)->getAttr('__value__')
        );
    }

    /**
     * Determines if the selector is an attribute setter (ends with ':').
     *
     * @param string $selector The selector to check.
     * 
     * @return bool Whether the selector is an attribute setter.
     */
    private function isAttributeSetter(string $selector): bool
    {
        return str_ends_with($selector, ':');
    }

    /**
     * Sets an attribute value based on the sender object.
     *
     * @param string $selector The selector (attribute name).
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * @param Scope $scope The current execution scope.
     * 
     * @return SOL25Object The current object.
     */
    private function setAttributeFromSender(string $selector, ?array $senderObj, Scope $scope): SOL25Object
    {
        $this->setAttr(rtrim($selector, ':'), $senderObj[0]->evaluate($scope));
        return $this;
    }

    /**
     * Retrieves the attribute value or throws an error if not found.
     *
     * @param string $selector The attribute to retrieve.
     * 
     * @return SOL25Object The attribute value.
     * @throws Exception If the attribute is not found.
     */
    private function getAttributeOrThrow(string $selector): SOL25Object
    {
        $attrValue = $this->getAttr($selector);
        if ($attrValue !== null) {
            return $attrValue;
        }

        throw new Exception("Attribute '$selector' not found in class or parent.\n", ReturnCode::INTERPRET_DNU_ERROR);
    }

    /**
     * Handles user-defined methods by evaluating their blocks.
     * 
     * @param array{method: Method, type: string, class?: mixed} $methodInfo The method information.
     * @param SOL25Object $receiverObj The receiver object for the method.
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the method evaluation.
     */
    private function handleUserMethod(array $methodInfo, SOL25Object $receiverObj, Scope $scope, ?array $senderObj): SOL25Object
    {
        $scope->setSelf($receiverObj);
        $superInstance = new SOL25Object($receiverObj->class->parent);
        $scope->setSuper($superInstance);

        // Evaluate the method's block with or without sender objects
        return isset($senderObj[0])
            ? $methodInfo['method']->block->evaluate($scope, $senderObj)
            : $methodInfo['method']->block->evaluate($scope);
    }

    /**
     * Handles built-in methods based on method info.
     * 
     * @param array{method: Method, type: string, class?: mixed} $methodInfo The method information.
     * @param SOL25Object $receiverObj The receiver object.
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the method evaluation.
     */
    private function handleMethodType(array $methodInfo, SOL25Object $receiverObj, Scope $scope, ?array $senderObj): SOL25Object
    {
        if ($methodInfo['type'] === 'user') {
            return $this->handleUserMethod($methodInfo, $receiverObj, $scope, $senderObj);
        }

        // Handle built-in method call
        return $methodInfo['type'] === 'builtin'
            ? $methodInfo['class']->switchMethod($receiverObj, $methodInfo['method'], $scope, $senderObj)
            : $scope->fetchSingleton('nil');
    }
}
