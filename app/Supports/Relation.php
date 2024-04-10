<?php

namespace App\Supports;

use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;

class Relation
{
    /**
     * @param string $targetType
     * @param int $targetId
     * @param array $filterAttributes
     * @return Model
     */
    public static function firstOrFail(string $targetType, int $targetId, array $filterAttributes = []): Model
    {
        /** @var Model $targetClass */
        $targetClass = Relation::getMorphedModel($targetType);
        if (empty($targetClass)) {
            throw new \RuntimeException("{$targetType} not defined morphed alias.");
        }

        if (method_exists($targetClass, '__find')) {
            $info = call_user_func([$targetClass, '__find'], $targetId, $targetType);
            if (empty($info)) {
                throw (new ModelNotFoundException())->setModel($targetClass);
            }

            return $info;
        } else {
            return $targetClass::query()->where(array_merge([
                'id' => $targetId,
            ], $filterAttributes))->firstOrFail();
        }
    }

    /**
     * @param string $targetType
     * @param int $targetId
     * @param array $filterAttributes
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function first($targetType, $targetId, $filterAttributes = [])
    {
        /** @var Model $targetClass */
        $targetClass = Relation::getMorphedModel($targetType);
        if (empty($targetClass)) {
            throw new \RuntimeException("{$targetType} not defined morphed alias.");
        }

        if (method_exists($targetClass, '__find')) {
            return call_user_func([$targetClass, '__find']);
        } else {
            return $targetClass::query()->where(array_merge([
                'id' => $targetId,
            ], $filterAttributes))->first();
        }
    }

}
