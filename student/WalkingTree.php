<?php

/**
 * @author Miroslav Basista (xbasism00)
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Student\AstTree\AstProgram;
use IPP\Student\Sol25\SolBlockClass;
use IPP\Student\Sol25\SolClass;
use IPP\Student\Sol25\SolFalseClass;
use IPP\Student\Sol25\SolIntegerClass;
use IPP\Student\Sol25\SolMethod;
use IPP\Student\Sol25\SolNilClass;
use IPP\Student\Sol25\SolObject;
use IPP\Student\Sol25\SolObjectClass;
use IPP\Student\Sol25\SolStringClass;
use IPP\Student\Sol25\SolTrueClass;
use IPP\Student\Scopes;

class WalkingTree
{
    public function initialization(Scopes $scope): void
    {
        // Create builtin classes and register them
        $objectClass = new SolObjectClass('Object');
        $intClass = new SolIntegerClass('Integer', $objectClass);
        $stringClass = new SolStringClass('String', $objectClass);
        $trueClass = new SolTrueClass('True', $objectClass);
        $falseClass = new SolFalseClass('False', $objectClass);
        $nilClass = new SolNilClass('Nil', $objectClass);
        $blockClass = new SolBlockClass('Block', $objectClass);

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
        $trueObj = new SolObject($trueClass);
        $falseObj = new SolObject($falseClass);
        $nilObj = new SolObject($nilClass);

        // Register singleton objects
        $scope->setSingleton('true', $trueObj);
        $scope->setSingleton('false', $falseObj);
        $scope->setSingleton('nil', $nilObj);
    }

    public function traverseProgram(AstProgram $program, string $input = ''): Scopes
    {
        $scope = new Scopes($input);
        $this->initialization($scope);

        // Traverse classes in the program
        foreach ($program->classes_list as $className => $classDef) {
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
            $solClass = new SolClass($classDef->name, $parent);
            foreach ($classDef->methods_list as $methodName => $methodDef) {
                $solMethod = new SolMethod($methodDef->selector_name, $methodDef->method_body, $methodDef->method_body->params);
                $solClass->addMethod($solMethod);
            }

            // Register class in scope
            $scope->registerClass($solClass->name, $solClass);
        }

        return $scope;
    }
}
