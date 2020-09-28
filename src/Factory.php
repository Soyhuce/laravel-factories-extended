<?php

namespace Soyhuce\LaravelFactoriesExtended;

use Illuminate\Database\Eloquent\Factories\Factory as BaseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

abstract class Factory extends BaseFactory
{
    /**
     * Define a parent model for the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null $relationship
     * @return static
     */
    public function of(Model $model, $relationship = null)
    {
        $relationship = $relationship ?: Str::camel(class_basename($model));
        $relationship = $this->newModel()->{$relationship}();

        return $this->state($relationship instanceof MorphTo ? [
            $relationship->getMorphType() => $model->getMorphClass(),
            $relationship->getForeignKeyName() => $model->getKey(),
        ] : [
            $relationship->getForeignKeyName() => $model->getKey(),
        ]);
    }

    /**
     * Proxy dynamic factory methods onto their proper methods.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'of')) {
            $relationship = Str::camel(Str::substr($method, 2));

            return $this->of($parameters[0], $relationship);
        }

        return parent::__call($method, $parameters);
    }
}
