<?php

namespace Devfrey\RectorLaravel\Eloquent;

use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\NodeManipulator\ClassManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Tests\Eloquent\AddGenericHasBuilderTraitRector\AddGenericHasBuilderTraitRectorTest
 */
final class AddGenericHasBuilderTraitRector extends ModelRector
{
    /**
     * Create a new Rector rule instance.
     *
     * @param \Rector\NodeManipulator\ClassManipulator $classManipulator
     * @return void
     */
    public function __construct(
        private readonly ClassManipulator $classManipulator,
    ) {
    }

    /**
     * Get the rule definition for the Rector rule.
     *
     * @return \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('// @todo fill the description', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// @todo fill code before
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// @todo fill code after
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
        if ($this->classManipulator->hasTrait($model, 'Illuminate\Database\Eloquent\HasBuilder')) {
            return null;
        }

        $builderClassName = $this->detectBuilderClassName($model);

        if (is_null($builderClassName)) {
            return null;
        }

        $model->stmts = [
            $this->createTraitUse($builderClassName),
            ...$model->stmts,
        ];

        return $model;
    }

    /**
     * Detect the given model's builder name.
     *
     * @param \PhpParser\Node\Stmt\Class_ $model
     * @return \PhpParser\Node\Name\FullyQualified|null
     */
    private function detectBuilderClassName(Class_ $model): ?FullyQualified
    {
        // First we check if the model has a newEloquentBuilder() method
        $newEloquentBuilder = $model->getMethod('newEloquentBuilder');

        if (!is_null($newEloquentBuilder)) {
            $returnType = $newEloquentBuilder->getReturnType();

            if ($returnType instanceof FullyQualified) {
                return $returnType;
            }

            // If the return type cannot be determined, we immediately abort
            // and skip looking for the $builder property. Eloquent only uses
            // the $builder property if the newEloquentBuilder() method
            // is missing.
            return null;
        }

        // Otherwise, we check if the model has a protected static $builder property
        foreach ($model->getProperties() as $property) {
            if (
                !$property->isProtected()
                || !$property->isStatic()
                || !$this->isName($property, 'builder')
            ) {
                continue;
            }

            $default = $property->props[0]->default;

            if (
                $default instanceof ClassConstFetch
                && $default->class instanceof FullyQualified
            ) {
                return $default->class;
            }

            // If the $builder property is not a class constant fetch, we
            // cannot determine the builder type.
            return null;
        }

        return null;
    }

    /**
     * Create a new trait use statement.
     *
     * @param \PhpParser\Node\Name\FullyQualified $builderClassName
     * @return \PhpParser\Node\Stmt\TraitUse
     */
    private function createTraitUse(FullyQualified $builderClassName): TraitUse
    {
        $traitUse = new TraitUse([new FullyQualified('Illuminate\Database\Eloquent\HasBuilder')]);
        $traitUse->setDocComment(
            new Doc(
                <<<PHPDOC
                /**
                 * @use \Illuminate\Database\Eloquent\HasBuilder<{$builderClassName->toCodeString()}>
                 */
                PHPDOC,
            ),
        );

        return $traitUse;
    }
}
