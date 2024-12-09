<?php


namespace Plugins\Order\App\Http\Requests;

use App\Exceptions\Error;
use Illuminate\Validation\ValidationException;
use Xin\LaravelFortify\Request\FormRequest;

class FreightTemplateRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title'    => 'required|between2,48',
        'fee_type' => 'required|in:0,1,2',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'    => '模板名称',
        'fee_type' => '计费方式',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

    /**
     * 验证数据合法性
     *
     * @param array $data
     * @param string $scene
     * @return array
     * @throws ValidationException
     */
    protected function validateData($data, $scene)
    {
        $data = $this->validateData2($data, $scene);

        if (!isset($data['rules'])) {
            throw Error::validationException("请配置区域规则");
        }

        $data['rules'] = json_decode($data['rules'], true);
        foreach ($data['rules'] as $key => $rule) {
            $validate = new FreightTemplateRuleRequest();

            try {
                $validate->failException(true)->check($rule);
            } catch (ValidationException $e) {
                $key += 1;
                throw Error::validationException("【{$key}】" . $e->getMessage());
            }
        }

        return $data;
    }
}
