<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\AstTree\AstAssignment;
use IPP\Student\AstTree\AstBlock;
use IPP\Student\AstTree\AstClassDefinition;
use IPP\Student\AstTree\AstLiteral;
use IPP\Student\AstTree\AstMessage;
use IPP\Student\AstTree\AstMethod;
use IPP\Student\AstTree\AstProgram;
use IPP\Student\AstTree\AstVariable;
use DOMDocument;
use DOMElement;

class DomParser
{
    // Parse the root <program> and create an AstProgram
    public function createProgram(DOMDocument $dom): AstProgram
    {
        $program = new AstProgram();

        // Iterate over all <class> elements
        $classes = $dom->getElementsByTagName('class');
        foreach ($classes as $class) {
            $c = new AstClassDefinition(
                $class->getAttribute('name'),
                $class->getAttribute('parent')
            );

            // Iterate over all <method> elements inside the class
            $methods = $class->getElementsByTagName('method');
            foreach ($methods as $method) {
                $block = $method->getElementsByTagName('block')->item(0);
                if ($block !== null) {
                    // Create an AstMethod with selector and parsed block
                    $m = new AstMethod(
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

    // Parse a <block> node and return an AstBlock
    public function createBlock(DOMElement $block_node): AstBlock
    {
        $block = new AstBlock((int) $block_node->getAttribute('arity'));

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
                    $variable = $varNode->getAttribute('name');
                    $expression = $this->createExpression($exprNode);
                    $assignments[$order] = new AstAssignment($variable, $expression);
                }
            }
        }

        ksort($assignments); // Sort assignments by order
        $block->instructions = array_values($assignments);

        return $block;
    }

    // Parse an <expr> node and return an AST object
    public function createExpression(DOMElement $expr_node): AstBlock|AstLiteral|AstMessage|AstVariable
    {
        foreach ($expr_node->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            switch ($child->nodeName) {
                case 'literal':
                    // Return a literal node
                    return new AstLiteral(
                        $child->getAttribute('class'),
                        $child->getAttribute('value')
                    );

                case 'var':
                    // Return a variable node
                    return new AstVariable($child->getAttribute('name'));

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

                    return new AstMessage(
                        $receiver,
                        $child->getAttribute('selector'),
                        $args
                    );

                case 'block':
                    // Return a nested block expression
                    return $this->createBlock($child);

                default:
                    // Unknown node inside <expr>
                    throw new InterDException('Unknown node in <expr>', ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
            }
        }

        // No valid child found in <expr>
        throw new InterDException('Unknown node in <expr>', ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
    }
}
