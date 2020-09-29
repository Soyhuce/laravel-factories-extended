<?php

namespace Soyhuce\LaravelFactoriesExtended;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Soyhuce\LaravelFactoriesExtended\Factory as FactoryExtended;
use Soyhuce\NextIdeHelper\Domain\Factories\Actions\FactoryResolver;
use Soyhuce\NextIdeHelper\Domain\Factories\Entities\Factory;
use Soyhuce\NextIdeHelper\Domain\Models\Entities\Relation;

class DynamicOfResolver implements FactoryResolver
{
    public function execute(Factory $factory): void
    {
        if (!$factory->instance() instanceof FactoryExtended) {
            return;
        }

        $factory->model->relations
            ->filter(function (Relation $relation) {
                return $relation->eloquentRelation() instanceof BelongsTo;
            })
            ->each(function (Relation $relation) use ($factory) {
                $factory->addMethod($this->dynamicOf($factory, $relation));
            });
    }

    private function dynamicOf(Factory $factory, Relation $relation): string
    {
        return sprintf(
            '%s of%s(%s $%s)',
            $factory->fqcn,
            Str::studly($relation->name),
            $relation->related->fqcn,
            Str::camel(class_basename($relation->related->fqcn))
        );
    }
}
