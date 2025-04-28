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

        // Handle case where no method was found
        if (!$methodInfo) {
            return $this->handleNoMethodFound($receiverObj, $selector, $scope, $senderObj);
        }

        // Handle user-defined method call
        if ($methodInfo['type'] === 'user') {
            return $this->handleUserMethod($receiverObj, $methodInfo, $scope, $senderObj);
        }

        // Handle built-in method call
        if ($methodInfo['type'] === 'builtin') {
            return $methodInfo['class']->switchMethod($receiverObj, $selector, $scope, $senderObj);
        }

        // Return 'nil' if method type is not recognized
        return $scope->getSingleton('nil');
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
        if ($selector === 'value' && $this->class instanceof SOL25Block) {
            return $receiverObj->getAttr('__value__')->evaluate($scope, null);
        }

        if (str_starts_with($selector, 'value:') && $this->class instanceof SOL25Block) {
            return $receiverObj->getAttr('__value__')->evaluate($scope, $senderObj);
        }

        // Handle 'from:' selector for creating a new SOL25Object from sender value
        if (str_starts_with($selector, 'from:')) {
            return new SOL25Object($scope->getClass($this->class->name), $senderObj[0]->evaluate($scope)->getAttr('__value__'));
        }

        // Handle 'new' selector to return a new instance of the class
        if ($selector === 'new') {
            return $this->handleNewSelector($scope);
        }

        // Handle attribute setting with 'selector:' format
        if (str_ends_with($selector, ':')) {
            $this->setAttr(rtrim($selector, ':'), $senderObj[0]->evaluate($scope));
            return $this;
        }

        // Return attribute value or throw an error if not found
        $attrValue = $this->getAttr($selector);
        if ($attrValue !== null) {
            return $attrValue;
        }

        throw new Exception("Attribute '$selector' not found in class or parent.\n", ReturnCode::INTERPRET_DNU_ERROR);
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
                return $scope->getSingleton('false');
            case 'True':
                return $scope->getSingleton('true');
            case 'Nil':
                return $scope->getSingleton('nil');
            case 'String':
                return new SOL25Object($scope->getClass('String'), '');
            case 'Integer':
                return new SOL25Object($scope->getClass('Integer'), 0);
            default:
                return new SOL25Object($scope->getClass($this->class->name), null);
        }
    }

    /**
     * Handles user-defined methods by evaluating their blocks.
     * 
     * @param SOL25Object $receiverObj The receiver object for the method.
     * @param array{method: Method} $methodInfo The method information.
     * @param Scope $scope The current execution scope.
     * @param array<int, Message|Literal|Block|Variable|Method|SOL25Object>|null $senderObj
     * 
     * @return SOL25Object The result of the method evaluation.
     */
    private function handleUserMethod(SOL25Object $receiverObj, array $methodInfo, Scope $scope, ?array $senderObj): SOL25Object
    {
        $scope->setSelf($receiverObj);
        $superInstance = new SOL25Object($receiverObj->class->parent);
        $scope->setSuper($superInstance);

        // Evaluate the method's block with or without sender objects
        return isset($senderObj[0])
            ? $methodInfo['method']->block->evaluate($scope, $senderObj)
            : $methodInfo['method']->block->evaluate($scope);
    }
}
