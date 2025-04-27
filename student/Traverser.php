<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\Program;
use IPP\Student\SOL25Block;
use IPP\Student\SOL25Class;
use IPP\Student\SOL25False;
use IPP\Student\SOL25Integer;
use IPP\Student\SOL25Method;
use IPP\Student\SOL25Nil;
use IPP\Student\SOL25Object;
use IPP\Student\SOL25ObjectClass;
use IPP\Student\SOL25String;
use IPP\Student\SOL25True;
use IPP\Student\Scope;

class Traverser
{
    public function initialize(Scope $scope): void
    {
        // Create builtin classes and register them
        $objectClass = new SOL25ObjectClass('Object');
        $intClass = new SOL25Integer('Integer', $objectClass);
        $stringClass = new SOL25String('String', $objectClass);
        $trueClass = new SOL25True('True', $objectClass);
        $falseClass = new SOL25False('False', $objectClass);
        $nilClass = new SOL25Nil('Nil', $objectClass);
        $blockClass = new SOL25Block('Block', $objectClass);

        $objectClassMethods = ['identicalTo:', 'equalTo:', 'asString', 'isNumber', 'isString', 'isBlock', 'isNil'];
        $intClassMethods = array_merge($objectClassMethods, ['greaterThan:', 'plus:', 'minus:', 'multiplyBy:', 'divBy:', 'asInteger', 'timesRepeat:']);
        $stringClassMethods = array_merge($objectClassMethods, ['read', 'print', 'asInteger', 'concatenateWith:', 'startsWith:endsBefore:']);
        $trueFalseClassMethods = array_merge($objectClassMethods, ['not', 'and:', 'or:', 'ifTrue:ifFalse:']);
        $blockClassMethods = array_merge($objectClassMethods, ['whileTrue']);

        // Add methods to corresponding classes
        foreach ($objectClassMethods as $selector) {
            $objectClass->addBuiltInMethod($selector);
            $nilClass->addBuiltInMethod($selector);
        }
        foreach ($intClassMethods as $selector) {
            $intClass->addBuiltInMethod($selector);
        }
        foreach ($stringClassMethods as $selector) {
            $stringClass->addBuiltInMethod($selector);
        }
        foreach ($trueFalseClassMethods as $selector) {
            $trueClass->addBuiltInMethod($selector);
            $falseClass->addBuiltInMethod($selector);
        }
        foreach ($blockClassMethods as $selector) {
            $blockClass->addBuiltInMethod($selector);
        }

        // Register classes in scope
        $scope->registerClass('Object', $objectClass);
        $scope->registerClass('Integer', $intClass);
        $scope->registerClass('String', $stringClass);
        $scope->registerClass('True', $trueClass);
        $scope->registerClass('False', $falseClass);
        $scope->registerClass('Nil', $nilClass);
        $scope->registerClass('Block', $blockClass);

        // Create singleton objects
        $trueObj = new SOL25Object($trueClass);
        $falseObj = new SOL25Object($falseClass);
        $nilObj = new SOL25Object($nilClass);

        // Register singleton objects
        $scope->setSingleton('true', $trueObj);
        $scope->setSingleton('false', $falseObj);
        $scope->setSingleton('nil', $nilObj);
    }

    public function traverseProgram(Program $program, string $input = ''): Scope
    {
        $scope = new Scope($input);
        $this->initialize($scope);

        // Traverse classes in the program
        foreach ($program->classes as $className => $classDef) {
            $parent = null;

            // Ensure 'Object' class has no parent
            if (empty($classDef->parent)) {
                if ($className !== 'Object') {
                    throw new InterDException("Class '{$className}' must have a parent (except for 'Object')", ReturnCode::INTERPRET_TYPE_ERROR);
                }
            } else {
                // Get parent class from scope
                $parentClass = $scope->getClass($classDef->parent);
                $parent = $parentClass;
            }

            // Create class and add its methods
            $class = new SOL25Class($classDef->name, $parent);
            foreach ($classDef->methods as $methodName => $methodDef) {
                $method = new SOL25Method($methodDef->selector_name, $methodDef->method_body, $methodDef->method_body->params);
                $class->addMethod($method);
            }

            // Register class in scope
            $scope->registerClass($class->name, $class);
        }

        return $scope;
    }
}
