<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Parser.php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Assignment;
use IPP\Student\Block;
use IPP\Student\ClassDefinition;
use IPP\Student\Literal;
use IPP\Student\Message;
use IPP\Student\Method;
use IPP\Student\Program;
use IPP\Student\Variable;
use DOMDocument;
use DOMElement;

/**
 * Parses XML data into the internal structure of a program, including classes, methods, blocks, and expressions.
 */
class Parser
{
    /**
     * Creates a Program object from a DOMDocument.
     *
     * @param DOMDocument $dom The DOMDocument containing the XML data.
     * @return Program The parsed Program object.
     */
    public function createProgram(DOMDocument $dom): Program
    {
        $program = new Program();

        foreach ($dom->getElementsByTagName('class') as $class) {
            $classDefinition = $this->parseClass($class);
            $program->addClass($classDefinition);
        }

        return $program;
    }

    /**
     * Parses a class node into a ClassDefinition object.
     *
     * @param DOMElement $class The class XML element.
     * @return ClassDefinition The parsed ClassDefinition object.
     */
    private function parseClass(DOMElement $class): ClassDefinition
    {
        $classDefinition = new ClassDefinition(
            $class->getAttribute('name'),
            $class->getAttribute('parent')
        );

        foreach ($class->getElementsByTagName('method') as $method) {
            $methodDefinition = $this->parseMethod($method);
            $classDefinition->addMethod($methodDefinition);
        }

        return $classDefinition;
    }

    /**
     * Parses a method node into a Method object.
     *
     * @param DOMElement $method The method XML element.
     * @return Method The parsed Method object.
     */
    private function parseMethod(DOMElement $method): Method
    {
        $blockNode = $method->getElementsByTagName('block')->item(0);
        if ($blockNode) {
            return new Method(
                $method->getAttribute('selector'),
                $this->createBlock($blockNode)
            );
        }
        throw new Exception("Method block is missing", ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
    }

    /**
     * Creates a Block object from a <block> node.
     *
     * @param DOMElement $blockNode The block XML element.
     * @return Block The parsed Block object.
     */
    public function createBlock(DOMElement $blockNode): Block
    {
        $block = new Block((int) $blockNode->getAttribute('arity'));
        $block->params = $this->parseBlockParameters($blockNode);
        $block->instructions = $this->parseAssignments($blockNode);
        return $block;
    }

    /**
     * Parses the parameters of a block and returns them in order.
     *
     * @param DOMElement $blockNode The block XML element.
     * @return array<int, string> The parsed parameters.
     */
    private function parseBlockParameters(DOMElement $blockNode): array
    {
        $params = [];
        foreach ($blockNode->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === 'parameter') {
                $order = (int) $child->getAttribute('order');
                $params[$order] = $child->getAttribute('name');
            }
        }

        ksort($params); // Ensure parameters are in order
        return array_values($params);
    }

    /**
     * Parses the assignment statements inside a block.
     *
     * @param DOMElement $blockNode The block XML element.
     * @return array<int, Assignment> The parsed assignment statements.
     */
    private function parseAssignments(DOMElement $blockNode): array
    {
        $assignments = [];
        foreach ($blockNode->getElementsByTagName('assign') as $statement) {
            if ($statement->parentNode->isSameNode($blockNode)) {
                $order = (int) $statement->getAttribute('order');
                $varNode = $statement->getElementsByTagName('var')->item(0);
                $exprNode = $statement->getElementsByTagName('expr')->item(0);

                if ($varNode && $exprNode) {
                    $var = $varNode->getAttribute('name');
                    $expression = $this->createExpression($exprNode);
                    $assignments[$order] = new Assignment($var, $expression);
                }
            }
        }

        ksort($assignments); // Ensure assignments are in order
        return array_values($assignments);
    }

    /**
     * Creates an expression object from an <expr> node.
     *
     * @param DOMElement $exprNode The expression XML element.
     * @return Block|Literal|Message|Variable The parsed expression.
     * @throws Exception If the expression is invalid.
     */
    public function createExpression(DOMElement $exprNode): Block|Literal|Message|Variable
    {
        foreach ($exprNode->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            switch ($child->nodeName) {
                case 'literal':
                    return new Literal(
                        $child->getAttribute('class'),
                        $child->getAttribute('value')
                    );

                case 'var':
                    return new Variable($child->getAttribute('name'));

                case 'send':
                    return $this->parseMessageSend($child);

                case 'block':
                    return $this->createBlock($child);

                default:
                    throw new Exception('Unknown node in <expr>', ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
            }
        }

        throw new Exception('Unknown node in <expr>', ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
    }

    /**
     * Parses a message sending expression from a <send> node.
     *
     * @param DOMElement $sendNode The <send> XML element.
     * @return Message The parsed message.
     */
    private function parseMessageSend(DOMElement $sendNode): Message
    {
        $receiver = null;
        $args = [];

        foreach ($sendNode->childNodes as $sendChild) {
            if ($sendChild instanceof DOMElement) {
                if ($sendChild->nodeName === 'expr') {
                    if (!$receiver) {
                        $receiver = $this->createExpression($sendChild);
                    }
                } elseif ($sendChild->nodeName === 'arg') {
                    $order = (int) $sendChild->getAttribute('order');
                    $exprNode = $sendChild->getElementsByTagName('expr')->item(0);
                    if ($exprNode instanceof DOMElement) {
                        $args[$order] = $this->createExpression($exprNode);
                    }
                }
            }
        }

        ksort($args); // Ensure arguments are in order
        return new Message($receiver, $sendNode->getAttribute('selector'), array_values($args));
    }
}
