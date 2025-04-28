<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Interpreter.php

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
            // Load, parse the source and traverse the AST
            $scope = $this->prepareScope();

            // Evaluate the program using the created scope
            $this->evaluateProgram($scope);

            return ReturnCode::OK; // Return success code
        }
        catch (IPPException $e) {
            // Log error and exit with exception code
            $this->handleError($e);
            return ReturnCode::INTERNAL_ERROR; // This line will never be reached, put here to satisfy PHPStan
        }
    }

    /**
     * Prepares the scope by loading and parsing the program.
     *
     * @return mixed The scope for the program evaluation.
     */
    private function prepareScope()
    {
        // Load and parse the XML document
        $dom = $this->source->getDOMDocument();
        $parser = new Parser();
        $tree = $parser->createProgram($dom);

        // Read the input string
        $inputString = $this->input->readString() ?? ''; // Ensure non-null value

        // Traverse the AST to create a scope for evaluation
        $traverser = new Traverser();
        return $traverser->traverseProgram($tree, $inputString);
    }

    /**
     * Evaluates the program using the given scope.
     *
     * @param mixed $scope The scope created from the AST traversal.
     */
    private function evaluateProgram($scope): void
    {
        $eval = new Evaluate($scope);
        $eval->evaluate();
    }

    /**
     * Handles errors by logging the message and exiting with the exception code.
     *
     * @param IPPException $e The exception to handle.
     */
    private function handleError(IPPException $e): void
    {
        $this->stderr->writeString('Error: ' . $e->getMessage() . "\n");
        exit($e->getCode()); // Exit with the exception return code
    }
}
