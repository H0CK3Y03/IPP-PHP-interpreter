<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student\Sol25;

use IPP\Student\AstTree\AstBlock;
use IPP\Student\AstTree\AstLiteral;
use IPP\Student\AstTree\AstMessage;
use IPP\Student\AstTree\AstMethod;
use IPP\Student\AstTree\AstVariable;
use IPP\Student\Scopes;
use IPP\Student\InterDException;
use IPP\Core\ReturnCode;

class SolObject
{
    public SolClass $class;

    /**
     * @var array<string, mixed>
     */
    public array $attributes = [];

    public function __construct(SolClass $class, mixed $value = null)
    {
        $this->class = $class;
        $this->setAttr('__value__', $value);
    }

    // Sets name and the value of an attribute
    public function setAttr(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    // Gets the value of an attribute
    public function getAttr(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /** Sends a message to another object
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|SolObject> $senderObjects
     * @return SolObject
     */
    public function sendMessage(SolObject $receiverObject, string $selector, Scopes $scope, ?array $senderObjects): SolObject
    {
        // echo $selector;
        $methodInfo = $this->class->getMethod($selector);
        if (!$methodInfo) {
            if ($selector == 'value' && $this->class instanceof SolBlockClass) {
                return $receiverObject->getAttr('__value__')->evaluate($scope, null);
            }

            if (str_starts_with($selector, 'value:') && $this->class instanceof SolBlockClass) {
                $expectedArgs = substr_count($selector, 'value:');
                return $receiverObject->getAttr('__value__')->evaluate($scope, $senderObjects);
            }
            if (str_starts_with($selector, 'from:')) {
                return new SolObject($scope->getClass($this->class->name), $senderObjects[0]->evaluate($scope)->getAttr('__value__'));
            }
            if ($selector == 'new') {
                switch ($this->class->name) {
                    case 'False':
                        return $scope->getSingleton('false');
                    case 'True':
                        return $scope->getSingleton('true');
                    case 'Nil':
                        return $scope->getSingleton('nil');
                    case 'String':
                        $defaultValue = '';
                        break;
                    case 'Integer':
                        $defaultValue = 0;
                        break;
                    default:
                        $defaultValue = null;
                }

                return new SolObject($scope->getClass(
                    $this->class->name,
                ), $defaultValue);
            }
            if (str_ends_with($selector, ':')) {
                $this->setAttr(rtrim($selector, ':'), $senderObjects[0]->evaluate($scope));
                return $this;
            } elseif ($this->getAttr($selector) != null) {
                return $this->getAttr($selector);
            } elseif ($this->getAttr($selector) === null) {
                throw new InterDException("Error: Attribute '$selector' not found in class or parent.\n", ReturnCode::INTERPRET_DNU_ERROR);
            }
        } elseif ($methodInfo['type'] === 'user') {
            $scope->setSelf($receiverObject);
            $superInstance = new SolObject($receiverObject->class->parent);
            $scope->setSuper($superInstance);
            if (!isset($senderObjects[0])) {
                return $methodInfo['method']->block->evaluate($scope);
            }
            return $methodInfo['method']->block->evaluate($scope, $senderObjects);
        } elseif ($methodInfo['type'] === 'builtin') {
            return $methodInfo['class']->switchMethod($receiverObject, $selector, $scope, $senderObjects);
        }
        return $scope->getSingleton('nil');
    }
}
