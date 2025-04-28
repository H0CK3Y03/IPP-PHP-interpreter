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
use IPP\Student\Exception;

class Traverser
{
    /**
     * Initialize the built-in classes, methods, and singleton objects in the provided scope.
     */
    public function initialize(Scope $scope): void
    {
        // Create built-in classes
        $objectClass = new SOL25ObjectClass('Object');
        $intClass = new SOL25Integer('Integer', $objectClass);
        $stringClass = new SOL25String('String', $objectClass);
        $trueClass = new SOL25True('True', $objectClass);
        $falseClass = new SOL25False('False', $objectClass);
        $nilClass = new SOL25Nil('Nil', $objectClass);
        $blockClass = new SOL25Block('Block', $objectClass);

        // Define method sets
        $objectMethods = ['identicalTo:', 'equalTo:', 'asString', 'isNumber', 'isString', 'isBlock', 'isNil'];
        $integerMethods = array_merge($objectMethods, ['greaterThan:', 'plus:', 'minus:', 'multiplyBy:', 'divBy:', 'asInteger', 'timesRepeat:']);
        $stringMethods = array_merge($objectMethods, ['read', 'print', 'asInteger', 'concatenateWith:', 'startsWith:endsBefore:']);
        $boolMethods = array_merge($objectMethods, ['not', 'and:', 'or:', 'ifTrue:ifFalse:']);
        $blockMethods = array_merge($objectMethods, ['whileTrue']);

        // Add methods to classes
        $this->addMethods($objectClass, $objectMethods);
        $this->addMethods($nilClass, $objectMethods);
        $this->addMethods($intClass, $integerMethods);
        $this->addMethods($stringClass, $stringMethods);
        $this->addMethods($trueClass, $boolMethods);
        $this->addMethods($falseClass, $boolMethods);
        $this->addMethods($blockClass, $blockMethods);

        // Register classes in scope
        $scope->registerClass('Object', $objectClass);
        $scope->registerClass('Integer', $intClass);
        $scope->registerClass('String', $stringClass);
        $scope->registerClass('True', $trueClass);
        $scope->registerClass('False', $falseClass);
        $scope->registerClass('Nil', $nilClass);
        $scope->registerClass('Block', $blockClass);

        // Create and register singleton objects
        $scope->storeSingleton('true', new SOL25Object($trueClass));
        $scope->storeSingleton('false', new SOL25Object($falseClass));
        $scope->storeSingleton('nil', new SOL25Object($nilClass));
    }

    /**
     * Helper to add built-in methods to a class.
     * @param string[] $methods
     */
    private function addMethods(SOL25ObjectClass $class, array $methods): void
    {
        foreach ($methods as $selector) {
            $class->addBuiltInMethod($selector);
        }
    }

    /**
     * Traverse a Program and set up all user-defined classes into a new scope.
     *
     * @throws Exception
     */
    public function traverseProgram(Program $program, string $input = ''): Scope
    {
        $scope = new Scope($input);
        $this->initialize($scope);

        foreach ($program->classes as $className => $classDef) {
            $parent = null;

            if (empty($classDef->parent)) {
                if ($className !== 'Object') {
                    throw new Exception(
                        "Class '{$className}' must have a parent (except for 'Object')",
                        ReturnCode::INTERPRET_TYPE_ERROR
                    );
                }
            } else {
                $parent = $scope->fetchClass($classDef->parent);
            }

            $class = new SOL25Class($classDef->name, $parent);

            foreach ($classDef->methods as $methodName => $methodDef) {
                $method = new SOL25Method(
                    $methodDef->selectorName,
                    $methodDef->block,
                    $methodDef->block->params
                );
                $class->addMethod($method);
            }

            $scope->registerClass($class->name, $class);
        }

        return $scope;
    }
}
