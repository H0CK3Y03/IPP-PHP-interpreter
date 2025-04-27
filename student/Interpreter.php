<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Student\WalkingTree;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        try {
            // Load the XML DOM document from the source file
            $dom = $this->source->getDOMDocument();

            // Create an AST from the XML document
            $domParser = new DomParser();
            $astTree = $domParser->createProgram($dom);

            // Read input string for the program
            $val = $this->input->readString();
            if ($val == null) {
                $val = '';
            }

            // Traverse the AST
            $walkTroughTree = new WalkingTree();
            $scope = $walkTroughTree->traverseProgram($astTree, $val);

            // Evaluate the program
            $evaluation = new Evaluate($scope);
            $evaluation->evaluate();

            return ReturnCode::OK;
        } catch (IPPException $e) {
            $this->stderr->writeString('Error: ' . $e->getMessage() . "\n");
            exit($e->getCode());
        }
    }
}
