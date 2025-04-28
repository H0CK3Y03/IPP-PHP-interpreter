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
        $blockClass = new SOL25Block('Block', $objectClass);
        $intClass = new SOL25Integer('Integer', $objectClass);
        $stringClass = new SOL25String('String', $objectClass);
        $trueClass = new SOL25True('True', $objectClass);
        $falseClass = new SOL25False('False', $objectClass);
        $nilClass = new SOL25Nil('Nil', $objectClass);

        // Define method sets
        $objectMethods = ['identicalTo:', 'equalTo:', 'asString', 'isNumber', 'isString', 'isBlock', 'isNil'];
        $blockMethods = array_merge($objectMethods, ['whileTrue']);
        $stringMethods = array_merge($objectMethods, ['read', 'print', 'asInteger', 'concatenateWith:', 'startsWith:endsBefore:']);
        $boolMethods = array_merge($objectMethods, ['not', 'and:', 'or:', 'ifTrue:ifFalse:']);
        $integerMethods = array_merge($objectMethods, ['greaterThan:', 'plus:', 'minus:', 'multiplyBy:', 'divBy:', 'asInteger', 'timesRepeat:']);

        // Add methods to classes
        $this->addMethods($objectClass, $objectMethods);
        $this->addMethods($blockClass, $blockMethods);
        $this->addMethods($intClass, $integerMethods);
        $this->addMethods($stringClass, $stringMethods);
        $this->addMethods($trueClass, $boolMethods);
        $this->addMethods($falseClass, $boolMethods);
        $this->addMethods($nilClass, $objectMethods);

        // Register classes in scope
        $scope->registerClass('Object', $objectClass);
        $scope->registerClass('Block', $blockClass);
        $scope->registerClass('Integer', $intClass);
        $scope->registerClass('String', $stringClass);
        $scope->registerClass('True', $trueClass);
        $scope->registerClass('False', $falseClass);
        $scope->registerClass('Nil', $nilClass);

        // Create and register singleton objects
        $scope->storeSingleton('true', new SOL25Object($trueClass));
        $scope->storeSingleton('false', new SOL25Object($falseClass));
        $scope->storeSingleton('nil', new SOL25Object($nilClass));
    }

    /**
     * Traverse a Program and set up all user-defined classes into a new scope.
     *
     * @throws Exception
     */
    public function traverseProgram(Program $program, string $input = ''): Scope
    {
        $scope = new Scope($input);
        $this->initialize($scope); // Initialize built-in classes and singletons

        // Traverse and register user-defined classes
        foreach ($program->classes as $className => $classDef) {
            $parent = $this->resolveParentClass($classDef, $scope);
            $class = new SOL25Class($classDef->name, $parent);

            $this->addMethodsToClass($class, $classDef->methods, $scope);

            $scope->registerClass($class->name, $class);
        }

        return $scope;
    }

    /**
     * Helper to add built-in methods to a class.
     *
     * @param SOL25ObjectClass $class The class to which the methods will be added.
     * @param string[] $methods The list of methods to add.
     */
    private function addMethods(SOL25ObjectClass $class, array $methods): void
    {
        foreach ($methods as $selector) {
            $class->addBuiltInMethod($selector);
        }
    }
    /**
     * Resolves the parent class for a given class definition.
     *
     * @param object $classDef The class definition.
     * @param Scope $scope The scope in which to resolve the parent.
     * @return SOL25Class|null The parent class, or null if no parent is specified.
     * @throws Exception If a non-Object class lacks a parent.
     */
    private function resolveParentClass(object $classDef, Scope $scope): ?SOL25Class
    {
        $parent = null;

        if (empty($classDef->parent)) {
            if ($classDef->name !== 'Object') {
                throw new Exception("Class '{$classDef->name}' must have a parent (except for 'Object')", ReturnCode::INTERPRET_TYPE_ERROR);
            }
        } else {
            $parent = $scope->fetchClass($classDef->parent);
        }

        return $parent;
    }
    /**
     * Add methods to a class from its class definition.
     *
     * @param SOL25Class $class The class to which methods are added.
     * @param array<string, Method> $methods The methods defined in the class (array of SOL25Method objects).
     * @param Scope $scope The current scope.
     */
    private function addMethodsToClass(SOL25Class $class, array $methods, Scope $scope): void
    {
        foreach ($methods as $methodName => $methodDef) {
            $method = new SOL25Method(
                $methodDef->selectorName,
                $methodDef->block,
                $methodDef->block->params
            );
            $class->addMethod($method);
        }
    }
}
