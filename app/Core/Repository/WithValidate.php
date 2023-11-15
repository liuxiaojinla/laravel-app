<?php

namespace App\Core\Repository;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait WithValidate
{

    /**
     * 生成验证器
     * @description return Validator::make($data, $rules = [], $messages = [], $customAttributes = []);
     * @param array $data
     * @param ValidatorMakeOptions $validatorMakeOptions
     * @return \Illuminate\Contracts\Validation\Validator
     */
    abstract protected function makeValidator(array $data, ValidatorMakeOptions $validatorMakeOptions);
//    {
//        return Validator::make($data, $rules = [], $messages = [], $customAttributes = []);
//    }

    /**
     * 验证数据合法性
     * @param string $useValidator
     * @param array $data
     * @param ValidatorMakeOptions $validatorMakeOptions
     * @return array
     * @throws ValidationException
     */
    protected function validate($useValidator, array $data, ValidatorMakeOptions $validatorMakeOptions)
    {
        $useValidator = $useValidator ?: 'default';
        $validators = $this->makeValidator($data, $validatorMakeOptions);
        $validators = is_array($validators) ? $validators : ['default' => $validators];

        // 判断验证器是否被定义
        if (!isset($validators[$useValidator])) {
            throw new \LogicException(static::class . "->makeValidator not define {$useValidator} Validator.");
        }

        $validator = $validators[$useValidator];
        if (!($validator instanceof ValidatorContract)) {
            throw new \LogicException(static::class . "->makeValidator must return a [" . ValidatorContract::class . "] instance.");
        }

        $validateData = $validator->validate();
        if (empty($validateData)) {
            throw new \LogicException('no data constraint defined!');
        }

        return $validateData;
    }

//    /**
//     * 要验证的数据
//     * @param mixed $data
//     * @param bool $isUpdate
//     * @return mixed
//     */
//    protected function validateData($data, $isUpdate)
//    {
//        return $data;
//    }
//    /**
//     * 要验证数据的规则
//     * @param bool $isUpdate
//     * @return array
//     */
//    protected function validateRules($isUpdate)
//    {
//        return [];
//    }
//
//    /**
//     * 要验证数据的提示信息
//     * @return array
//     */
//    protected function validateMessages()
//    {
//        return [];
//    }
//
//    /**
//     * 要验证数据的字段描述
//     * @return array
//     */
//    protected function validateFields()
//    {
//        return [];
//    }
}
