<?php


namespace Plugins\Order\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Http\Requests\FreightTemplateRequest;
use Plugins\Order\App\Models\FreightTemplate;
use Plugins\Order\App\Models\FreightTemplateRule;
use Xin\Hint\Facades\Hint;

class FreightTemplateController extends Controller
{


    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = FreightTemplate::simple()->search($search)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 数据详情
     * @param Request $request
     * @return Response
     */
    public function info(Request $request)
    {
        $id = $request->validId();
        $info = FreightTemplate::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(FreightTemplateRequest $request)
    {
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        /** @var FreightTemplate $info */
        $info = FreightTemplate::query()->create($data);
        $this->afterCreate($info, $data);

        return Hint::success("创建成功！", (string)url('index'), $info);
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
     * 更新数据
     * @return Response
     */
    public function update(FreightTemplateRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        /** @var FreightTemplate $info */
        $info = FreightTemplate::query()->where('id', $id)->firstOrFail();
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        $this->afterUpdate($info, $data);

        return Hint::success("更新成功！", (string)url('index'), $info);
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
        $existIds = FreightTemplateRule::query()->where('template_id', $model->id)->pluck('id')->toArray();
        $detachIds = array_diff($existIds, $ruleIds);

        foreach ($data['rules'] as $rule) {
            $model->rules()->save($rule);
        }

        FreightTemplateRule::query()->where('template_id', $model->id)
            ->where('id', 'in', $detachIds)->delete();
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force');

        FreightTemplate::query()->whereIn('id', $ids)->get()->each(function (FreightTemplate $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     * @throws ValidationException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->input($field);

        FreightTemplate::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }


}
