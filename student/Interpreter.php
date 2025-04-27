<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Student\Traverser;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        try {
            // Load the XML DOM document from the source file
            $dom = $this->source->getDOMDocument();

            // Create an AST from the XML document
            $parser = new Parser();
            $tree = $parser->createProgram($dom);

            // Read input string for the program
            $val = $this->input->readString();
            if ($val == null) {
                $val = '';
            }

            // Traverse the AST
            $traverser = new Traverser();
            $scope = $traverser->traverseProgram($tree, $val);

            // Evaluate the program
            $eval = new Evaluate($scope);
            $eval->evaluate();

            return ReturnCode::OK;
        } catch (IPPException $e) {
            $this->stderr->writeString('Error: ' . $e->getMessage() . "\n");
            exit($e->getCode());
        }
    }
}
