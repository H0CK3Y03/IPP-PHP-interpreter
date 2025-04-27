<?php

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

class Parser
{
    // Parse the root <program> and create an Program
    public function createProgram(DOMDocument $dom): Program
    {
        $program = new Program();

        // Iterate over all <class> elements
        $classes = $dom->getElementsByTagName('class');
        foreach ($classes as $class) {
            $c = new ClassDefinition(
                $class->getAttribute('name'),
                $class->getAttribute('parent')
            );

            // Iterate over all <method> elements inside the class
            $methods = $class->getElementsByTagName('method');
            foreach ($methods as $method) {
                $block = $method->getElementsByTagName('block')->item(0);
                if ($block !== null) {
                    // Create an Method with selector and parsed block
                    $m = new Method(
                        $method->getAttribute('selector'),
                        $this->createBlock($block)
                    );
                    $c->addMethod($m);
                }
            }

            // Add class to the program
            $program->addClass($c);
        }

        return $program;
    }

    // Parse a <block> node and return an Block
    public function createBlock(DOMElement $block_node): Block
    {
        $block = new Block((int) $block_node->getAttribute('arity'));

        // Parse block parameters
        $params = [];
        foreach ($block_node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === 'parameter') {
                $order = (int) $child->getAttribute('order');
                $params[$order] = $child->getAttribute('name');
            }
        }

        ksort($params); // Sort parameters by order
        $block->params = array_values($params);

        // Parse assignment instructions inside the block
        $assignments = [];
        $statements = $block_node->getElementsByTagName('assign');
        foreach ($statements as $statement) {
            if ($statement->parentNode->isSameNode($block_node)) {
                $order = (int) $statement->getAttribute('order');

                $varNode = $statement->getElementsByTagName('var')->item(0);
                $exprNode = $statement->getElementsByTagName('expr')->item(0);

                if ($varNode instanceof DOMElement && $exprNode instanceof DOMElement) {
                    $var = $varNode->getAttribute('name');
                    $expression = $this->createExpression($exprNode);
                    $assignments[$order] = new Assignment($var, $expression);
                }
            }
        }

        ksort($assignments); // Sort assignments by order
        $block->instructions = array_values($assignments);

        return $block;
    }

    // Parse an <expr> node and return an AST object
    public function createExpression(DOMElement $expr_node): Block|Literal|Message|Variable
    {
        foreach ($expr_node->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            switch ($child->nodeName) {
                case 'literal':
                    // Return a literal node
                    return new Literal(
                        $child->getAttribute('class'),
                        $child->getAttribute('value')
                    );

                case 'var':
                    // Return a Variable node
                    return new Variable($child->getAttribute('name'));

                case 'send':
                    // Handle message sending expression
                    $receiver = null;
                    $argMap = [];

                    foreach ($child->childNodes as $sendChild) {
                        if ($sendChild instanceof DOMElement) {
                            if ($sendChild->nodeName === 'expr') {
                                if ($receiver === null) {
                                    // First <expr> is the receiver
                                    $receiver = $this->createExpression($sendChild);
                                }
                            } elseif ($sendChild->nodeName === 'arg') {
                                // Parse message arguments
                                $order = (int) $sendChild->getAttribute('order');
                                $expr = $sendChild->getElementsByTagName('expr')->item(0);
                                if ($expr instanceof DOMElement) {
                                    $argMap[$order] = $this->createExpression($expr);
                                }
                            }
                        }
                    }

                    ksort($argMap); // Sort arguments by order
                    $args = array_values($argMap);

                    return new Message(
                        $receiver,
                        $child->getAttribute('selector'),
                        $args
                    );

                case 'block':
                    // Return a nested block expression
                    return $this->createBlock($child);

                default:
                    // Unknown node inside <expr>
                    throw new Exception('Unknown node in <expr>', ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
            }
        }

        // No valid child found in <expr>
        throw new Exception('Unknown node in <expr>', ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
    }
}
