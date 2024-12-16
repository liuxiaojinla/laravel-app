<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Mall\App\Http\Requests;

use Plugins\Mall\App\Models\GoodsCategory;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 商品验证器
 */
class GoodsRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '商品名称',
        'cover' => '商品封面',
        'picture' => '商品图册',
        'content' => '商品详情',
        'category_ids' => '所属分类',
        'is_multi_spec' => '商品规格类型',
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
     * @return array
     */
    public function validationData()
    {
        $data = parent::validationData();

        $data['spec_list'] = isset($data['spec_list']) ? json_decode($data['spec_list'], true) : [];
        $data['sku_list'] = isset($data['sku_list']) ? json_decode($data['sku_list'], true) : [];
        $data['service_ids'] = isset($data['service_ids']) ? $data['service_ids'] : [];

        $categoryIds = array_unique($data['category_ids']);

        $categoryId = $categoryIds[0];
        $parentCategoryId = GoodsCategory::query()->where('id', $categoryId)->value('pid');
        if ($parentCategoryId) {
            $data['category_id'] = $parentCategoryId;
            $data['category2_id'] = $categoryId;
        } else {
            $data['category_id'] = $categoryId;
        }

        return $data;
    }


    /**
     * 验证规则
     *
     * @return array[]
     */
    public function rules()
    {
        return [
            'title' => ['required', 'between:2,80'],
            'cover' => ['required'],
            'picture' => ['required', 'array'],
            'category_ids' => ['required'],
            'is_multi_spec' => ['required'],
            'content' => ['required'],
        ];
    }
}
