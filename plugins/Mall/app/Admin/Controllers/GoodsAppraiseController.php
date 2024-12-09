<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Admin\Controllers;

use App\Admin\Controller;
use Plugins\Mall\App\Models\GoodsAppraise;
use Xin\Hint\Facades\Hint;

class GoodsAppraiseController extends Controller
{
    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = GoodsAppraise::with([
            'user',
            'goods',
        ])->simple()->search($search)
            ->order([
                'id' => 'desc',
            ])
            ->paginate($this->request->paginate());


        return Hint::result($data);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        GoodsAppraise::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}