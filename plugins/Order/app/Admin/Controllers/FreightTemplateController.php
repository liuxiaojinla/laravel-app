<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\admin\controller;

use app\admin\Controller;
use app\common\model\Region;
use Plugins\Order\App\Http\Requests\FreightTemplateRuleValidate;
use Plugins\Order\App\Http\Requests\FreightTemplateValidate;
use Plugins\Order\App\Models\FreightTemplate;
use Plugins\Order\App\Models\FreightTemplateRule;
use think\db\Query;
use think\exception\ValidateException;

class FreightTemplateController extends Controller
{

    use InteractsCURD {
        InteractsCURD::showCreateView as showCreateView2;
        InteractsCURD::showUpdateView as showUpdateView2;
        InteractsCURD::validateData as validateData2;
    }

    /**
     * @var string
     */
    protected $model = FreightTemplate::class;

    /**
     * @var string
     */
    protected $validator = FreightTemplateValidate::class;

    /**
     * @var string[]
     */
    protected $allowFields = [
        'sort' => 'number|min:0',
    ];

    /**
     * 选择城市
     *
     * @return string
     */
    public function areas()
    {
        $data = Region::getTree(2);
        $this->assign('data', $data);

        return $this->fetch();
    }

    /**
     * @param Query $query
     */
    protected function querySelect(Query $query)
    {
        $query->removeOption('order')->order([
            'sort' => 'asc',
            'id'   => 'desc',
        ]);
    }

    /**
     * @return string
     */
    protected function showCreateView()
    {
        $this->assignRegions();

        return $this->showCreateView2();
    }

    /**
     */
    protected function assignRegions()
    {
        $this->assign('regions', Region::getList());
    }

    /**
     * @param FreightTemplate $model
     * @return string
     */
    protected function showUpdateView($model)
    {
        $this->assignRegions();

        return $this->showUpdateView2($model);
    }

    /**
     * 数据创建之后
     *
     * @param FreightTemplate $model
     * @param array $data
     */
    protected function afterCreate($model, $data)
    {
        foreach ($data['rules'] as $rule) {
            $model->rules()->save($rule);
        }
    }

    /**
     * 数据更新之后
     *
     * @param FreightTemplate $model
     * @param array $data
     */
    protected function afterUpdate($model, $data)
    {
        $ruleIds = array_column($data['rules'], 'id');
        $existIds = FreightTemplateRule::query()->where('template_id', $model->id)->column('id');
        $detachIds = array_diff($existIds, $ruleIds);

        foreach ($data['rules'] as $rule) {
            $model->rules()->save($rule);
        }

        FreightTemplateRule::query()->where('template_id', $model->id)
            ->where('id', 'in', $detachIds)->delete();
    }

    /**
     * 验证数据合法性
     *
     * @param array $data
     * @param string $scene
     * @return array
     */
    protected function validateData($data, $scene)
    {
        $data = $this->validateData2($data, $scene);

        if (!isset($data['rules'])) {
            throw Error::validationException("请配置区域规则");
        }

        $data['rules'] = json_decode($data['rules'], true);
        foreach ($data['rules'] as $key => $rule) {
            $validate = new FreightTemplateRuleValidate();

            try {
                $validate->failException(true)->check($rule);
            } catch (ValidateException $e) {
                $key += 1;
                throw Error::validationException("【{$key}】" . $e->getMessage());
            }
        }

        return $data;
    }

}
