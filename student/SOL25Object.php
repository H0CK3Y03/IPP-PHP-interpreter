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

    public function __construct(SOL25Class $class, mixed $val = null)
    {
        $this->class = $class;
        $this->setAttr('__value__', $val);
    }

    // Sets name and the value of an attribute
    public function setAttr(string $name, mixed $val): void
    {
        $this->attr[$name] = $val;
    }

    // Gets the value of an attribute
    public function getAttr(string $name): mixed
    {
        return $this->attr[$name] ?? null;
    }

    /** Sends a message to another object
     * @param array<Message|Literal|Block|Variable|Method|SOL25Object> $senderObj
     * @return SOL25Object
     */
    public function sendMessage(SOL25Object $receiverObj, string $selector, Scope $scope, ?array $senderObj): SOL25Object
    {
        // echo $selector;
        $methodInfo = $this->class->getMethod($selector);
        if (!$methodInfo) {
            if ($selector == 'value' && $this->class instanceof SOL25Block) {
                return $receiverObj->getAttr('__value__')->evaluate($scope, null);
            }

            if (str_starts_with($selector, 'value:') && $this->class instanceof SOL25Block) {
                $expectedArgs = substr_count($selector, 'value:');
                return $receiverObj->getAttr('__value__')->evaluate($scope, $senderObj);
            }
            if (str_starts_with($selector, 'from:')) {
                return new SOL25Object($scope->getClass($this->class->name), $senderObj[0]->evaluate($scope)->getAttr('__value__'));
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
                        $defaultVal = '';
                        break;
                    case 'Integer':
                        $defaultVal = 0;
                        break;
                    default:
                        $defaultVal = null;
                }

                return new SOL25Object($scope->getClass(
                    $this->class->name,
                ), $defaultVal);
            }
            if (str_ends_with($selector, ':')) {
                $this->setAttr(rtrim($selector, ':'), $senderObj[0]->evaluate($scope));
                return $this;
            } elseif ($this->getAttr($selector) != null) {
                return $this->getAttr($selector);
            } elseif ($this->getAttr($selector) === null) {
                throw new Exception("Error: Attribute '$selector' not found in class or parent.\n", ReturnCode::INTERPRET_DNU_ERROR);
            }
        } elseif ($methodInfo['type'] === 'user') {
            $scope->setSelf($receiverObj);
            $superInstance = new SOL25Object($receiverObj->class->parent);
            $scope->setSuper($superInstance);
            if (!isset($senderObj[0])) {
                return $methodInfo['method']->block->evaluate($scope);
            }
            return $methodInfo['method']->block->evaluate($scope, $senderObj);
        } elseif ($methodInfo['type'] === 'builtin') {
            return $methodInfo['class']->switchMethod($receiverObj, $selector, $scope, $senderObj);
        }
        return $scope->getSingleton('nil');
    }
}
