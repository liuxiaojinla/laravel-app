<?php

namespace app\api\controller;

use app\BaseController;
use think\db\Query;
use think\facade\Db;
use Xin\Hint\Facades\Hint;

class CommonController extends BaseController
{
    /**
     * 获取城市数据
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function regions()
    {
        $level = $this->request->param('level/d', 0);
        $pid = $this->request->param('pid/d', -1);

        $regions = Db::name('region')->when($level, function (Query $query) use ($level) {
            $query->where('level', '<=', $level);
        })->when($pid >= 0, function (Query $query) use ($pid) {
            $query->where('pid', '=', $pid);
        })->select();

        return Hint::result($regions);
    }
}