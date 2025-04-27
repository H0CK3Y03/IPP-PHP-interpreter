<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Student\Traverser;

/**
 * Interpreter for executing the parsed program in the student project.
 */
class Interpreter extends AbstractInterpreter
{
    /**
     * Executes the program by parsing, traversing, and evaluating it.
     *
     * @return int The return code indicating success or failure.
     */
    public function execute(): int
    {
        try {
            // Load the XML DOM document from the source file
            $dom = $this->source->getDOMDocument();

            // Create an AST (Abstract Syntax Tree) from the XML document
            $parser = new Parser();
            $tree = $parser->createProgram($dom);

            // Read the input string for the program
            $inputString = $this->input->readString();
            $inputString = $inputString ?? ''; // Ensure a non-null value

            // Traverse the AST to create a scope for evaluation
            $traverser = new Traverser();
            $scope = $traverser->traverseProgram($tree, $inputString);

            // Evaluate the program using the created scope
            $eval = new Evaluate($scope);
            $eval->evaluate();

            return ReturnCode::OK; // Return success code
        }
        catch (IPPException $e) {
            // Handle exceptions by logging the error message and exiting with the exception code
            $this->stderr->writeString('Error: ' . $e->getMessage() . "\n");
            exit($e->getCode()); // Exit with the exception return code
        }
    }
}
