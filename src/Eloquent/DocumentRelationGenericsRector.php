<?php

namespace Devfrey\RectorLaravel\Eloquent;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class DocumentRelationGenericsRector extends ModelRector
{
    private const SIMPLE_RELATIONS = [
        'belongsTo' => 'Illuminate\Database\Eloquent\Relations\BelongsTo',
        'belongsToMany' => 'Illuminate\Database\Eloquent\Relations\BelongsToMany',
        'hasMany' => 'Illuminate\Database\Eloquent\Relations\HasMany',
        'hasOne' => 'Illuminate\Database\Eloquent\Relations\HasOne',
        'morphMany' => 'Illuminate\Database\Eloquent\Relations\MorphMany',
        'morphOne' => 'Illuminate\Database\Eloquent\Relations\MorphOne',
        // 'morphTo' => 'Illuminate\Database\Eloquent\Relations\MorphTo',
        'morphToMany' => 'Illuminate\Database\Eloquent\Relations\MorphToMany',
    ];

    private const INTERMEDIATE_RELATIONS = [
        'hasManyThrough' => 'Illuminate\Database\Eloquent\Relations\HasManyThrough',
        'hasOneThrough' => 'Illuminate\Database\Eloquent\Relations\HasOneThrough',
    ];

    /**
     * Create a new Rector rule instance.
     *
     * @param \Rector\PhpParser\Node\BetterNodeFinder $betterNodeFinder
     * @param \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory
     * @param \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger
     * @return void
     */
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
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
        $modelType = $this->nodeTypeResolver->getType($model);

        if (!$modelType instanceof TypeWithClassName) {
            throw new ShouldNotHappenException();
        }

        $hasChanged = false;

        foreach ($model->getMethods() as $method) {
            $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($method, Return_::class);

            if (count($returns) !== 1) {
                continue;
            }

            $returnExpression = $returns[0]->expr;

            if (!$returnExpression instanceof MethodCall) {
                continue;
            }

            /// If the method call is chained, we take the first method call
            $methodCall = $this->getParentMethodCall($returnExpression);

            $genericRelationType = $this->parseMethodCallIntoGenericObjectTypeIfRelation($modelType, $methodCall);

            if (is_null($genericRelationType)) {
                continue;
            }

            $hasChanged = $this->phpDocTypeChanger->changeReturnType(
                functionLike: $method,
                phpDocInfo: $this->phpDocInfoFactory->createFromNodeOrEmpty($method),
                newType: $genericRelationType,
            ) || $hasChanged;
        }

        // Return the node if changes were made to avoid unnecessary re-parsing
        // of the node, which also causes issues.
        return $hasChanged ? $model : null;
    }

    /**
     * Parse a method call into a generic object type if it is a relation.
     *
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     * @return \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType|null
     */
    private function parseMethodCallIntoGenericObjectTypeIfRelation(
        TypeWithClassName $modelType,
        MethodCall $methodCall,
    ): ?FullyQualifiedGenericObjectType {
        $selfType = new SelfStaticType(
            $modelType->getClassReflection() ?? throw new ShouldNotHappenException(),
        );

        foreach (self::SIMPLE_RELATIONS as $relationMethod => $relationClass) {
            if (!$this->isName($methodCall->name, $relationMethod)) {
                continue;
            }

            $relatedType = $this->resolveFirstArgumentObjectType($methodCall);

            if (is_null($relatedType)) {
                return null;
            }

            return new FullyQualifiedGenericObjectType(
                $relationClass,
                [
                    $this->normalizeIntoStaticObjectType($relatedType),
                    $selfType,
                ],
            );
        }

        foreach (self::INTERMEDIATE_RELATIONS as $relationMethod => $relationClass) {
            if (!$this->isName($methodCall, $relationMethod)) {
                continue;
            }

            [$relatedType, $throughType] = $this->resolveFirstTwoArgumentObjectTypes($methodCall);

            if (is_null($relatedType) || is_null($throughType)) {
                return null;
            }

            return new FullyQualifiedGenericObjectType(
                $relationClass,
                [
                    $this->normalizeIntoStaticObjectType($relatedType),
                    $this->normalizeIntoStaticObjectType($throughType),
                    $selfType,
                ],
            );
        }

        if ($this->isName($methodCall->name, 'morphTo')) {
            return new FullyQualifiedGenericObjectType(
                'Illuminate\Database\Eloquent\Relations\MorphTo',
                [
                    new ObjectType('Illuminate\Database\Eloquent\Model'),
                    $selfType,
                ],
            );
        }

        return null;
    }

    /**
     * Normalize the given object type into a static object type.
     *
     * @template T of \PHPStan\Type\Type
     * @param \PHPStan\Type\ObjectType $type
     * @return ($type is \PHPStan\Type\ThisType ? \PHPStan\Type\ThisType : T)
     */
    private function normalizeIntoStaticObjectType(Type $type): ObjectType
    {
        return $type instanceof ThisType ? $type->getStaticObjectType() : $type;
    }

    /**
     * Resolve the first argument type from the given method call.
     *
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     * @return \PHPStan\Type\ObjectType|null
     */
    private function resolveFirstArgumentObjectType(MethodCall $methodCall)
    {
        $arguments = $methodCall->getArgs();

        if ($arguments === []) {
            return null;
        }

        return $this->resolveObjectTypeFromArgument($arguments[0]);
    }

    /**
     * Resolve the first two argument types from the given method call.
     *
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     * @return array{\PHPStan\Type\ObjectType|null, \PHPStan\Type\ObjectType|null}
     */
    private function resolveFirstTwoArgumentObjectTypes(MethodCall $methodCall): array
    {
        $arguments = $methodCall->getArgs();

        if (count($arguments) < 2) {
            return [null, null];
        }

        $firstArgument = $this->resolveObjectTypeFromArgument($arguments[0]);
        $secondArgument = $this->resolveObjectTypeFromArgument($arguments[1]);

        return [$firstArgument, $secondArgument];
    }

    /**
     * Resolve the object type from the given argument.
     *
     * @param \PhpParser\Node\Arg $argument
     * @return \PHPStan\Type\ObjectType|null
     */
    private function resolveObjectTypeFromArgument(Arg $argument): ?ObjectType
    {
        if (!$argument->value instanceof ClassConstFetch) {
            return null;
        }

        $resolvedType = $this->nodeTypeResolver->getType($argument->value->class);

        if (!$resolvedType instanceof ObjectType) {
            return null;
        }

        return $resolvedType;
    }

    /**
     * Get the parent method call of the given method call.
     *
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     * @return \PhpParser\Node\Expr\MethodCall
     */
    private function getParentMethodCall(MethodCall $methodCall): MethodCall
    {
        if ($methodCall->var instanceof MethodCall) {
            return $this->getParentMethodCall($methodCall->var);
        }

        return $methodCall;
    }
}
