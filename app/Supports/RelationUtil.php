<?php

namespace App\Supports;

use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationUtil
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


    /**
     * 检查的活动是否有效
     * @param string $targetType
     * @param int $targetId
     * @param array $filterAttributes
     * @return Model
     * @throws ValidationException
     */
    public static function checkActivityAvailable(string $targetType, int $targetId, array $filterAttributes = []): Model
    {
        $info = static::firstOrFail($targetType, $targetId, $filterAttributes);

        if (!DataAvailableUtil::isAvailable($info, null, $error)) {
            ValidationException::throwException("选择的活动无效：" . $error);
        }

        return $info;
    }

    /**
     * 检查一组活动是否有效
     * @param array $activity
     * @return array
     * @throws ValidationException
     */
    public static function checkActivityAvailableList(array $activity): array
    {
        $result = [];
        foreach ($activity as $key => $item) {
            if (empty($item['activity_type']) || empty($item['activity_id'])) {
                ValidationException::throwException('选择的第 ' . ($key + 1) . ' 个活动无效。');
            }

            $info = static::first($item['activity_type'], $item['activity_id']);
            if (empty($info)) {
                ValidationException::throwException('选择的第 ' . ($key + 1) . ' 个活动不存在！');
            }

            if (!DataAvailableUtil::isAvailable($info, null, $error)) {
                ValidationException::throwException('选择的第 ' . ($key + 1) . ' 个活动无效：' . $error);
            }

            $result[$key] = $info;
        }

        return $result;
    }

    /**
     * 检查的活动是否有效
     * @param mixed $info
     * @return mixed
     * @throws ValidationException
     */
    public static function checkAvailableByTarget($info)
    {
        if (!DataAvailableUtil::isAvailable($info, null, $error)) {
            ValidationException::throwException('选择的活动无效：' . $error);
        }

        return $info;
    }


}
