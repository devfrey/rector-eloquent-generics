<?php

namespace Devfrey\RectorLaravel\Eloquent;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;

abstract class ModelRector extends AbstractRector
{
    /**
     * Get the node types that the Rector rule refactors.
     *
     * @return array<class-string<\PhpParser\Node>>
     */
    final public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * Refactor the Eloquent model.
     *
     * @param \PhpParser\Node\Stmt\Class_ $model
     * @return \PhpParser\Node\Stmt\Class_|null
     */
    abstract public function refactorModel(Class_ $model): ?Class_;

    /**
     * Refactor the node.
     *
     * @param \PhpParser\Node $node
     * @return \PhpParser\Node\Stmt\Class_|null
     */
    final public function refactor(Node $node): ?Class_
    {
        if (!$node instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        if (!$this->isObjectType($node, new ObjectType('\Illuminate\Database\Eloquent\Model'))) {
            return null;
        }

        return $this->refactorModel($node);
    }
}
