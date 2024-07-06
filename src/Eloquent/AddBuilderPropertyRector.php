<?php

namespace Devfrey\RectorLaravel\Eloquent;

use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Tests\Eloquent\AddBuilderPropertyRector\AddBuilderPropertyRectorTest
 */
final class AddBuilderPropertyRector extends ModelRector
{
    /**
     * Get the rule definition for the Rector rule.
     *
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor newEloquentBuilder() to $builder', [
            new CodeSample(
                <<<'CODE_SAMPLE'
public function newEloquentBuilder($query): UserBuilder
{
    return new UserBuilder($query);
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
protected static string $builder = UserBuilder::class;
CODE_SAMPLE,
            ),
        ]);
    }

    /**
     * Refactor the Eloquent model.
     *
     * @param \PhpParser\Node\Stmt\Class_ $model
     * @return \PhpParser\Node\Stmt\Class_|null
     */
    public function refactorModel(Class_ $model): ?Class_
    {
        foreach ($model->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }

            if (!$this->isName($stmt, 'newEloquentBuilder')) {
                continue;
            }

            if (!$stmt->getReturnType() instanceof Name\FullyQualified) {
                // If the method does not have a native return type, we cannot
                // determine the builder class and therefore cannot refactor
                // the method to a property.
                continue;
            }

            // Remove the newEloquentBuilder() method
            unset($model->stmts[$key]);

            // Add the $builder property
            $this->insertAfterTraitUses(
                $model,
                $this->createProtectedStaticBuilderProperty($stmt->getReturnType()),
            );

            return $model;
        }

        return null;
    }

    /**
     * Insert the property after the trait uses.
     *
     * @param \PhpParser\Node\Stmt\Class_ $class
     * @param \PhpParser\Node\Stmt\Property $property
     * @return void
     */
    private function insertAfterTraitUses(Class_ $class, Node\Stmt\Property $property): void
    {
        $traitUseIndex = -1;

        foreach ($class->stmts as $index => $stmt) {
            if ($stmt instanceof Node\Stmt\TraitUse) {
                $traitUseIndex = $index;
            }
        }

        array_splice($class->stmts, $traitUseIndex + 1, 0, [$property]);
    }

    /**
     * Create a protected static $builder property.
     *
     * @param \PhpParser\Node\Name\FullyQualified $builderClass
     * @return \PhpParser\Node\Stmt\Property
     */
    private function createProtectedStaticBuilderProperty(Name\FullyQualified $builderClass): Node\Stmt\Property
    {
        // \App\Models\Builders\UserBuilder::class
        $default = $this->nodeFactory->createClassConstReference($builderClass->toString());

        // protected static $builder = \App\Models\Builders\UserBuilder::class
        return (new Property('builder'))
            ->makeProtected()
            ->makeStatic()
            ->setType('string')
            ->setDefault($default)
            ->getNode();
    }
}
