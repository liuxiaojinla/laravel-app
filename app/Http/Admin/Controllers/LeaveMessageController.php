<?php

namespace App\Http\Admin\Controllers;

use app\common\model\LeaveMessage;
use app\common\model\Model;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

class LeaveMessageController extends Controller
{
    public function index(Request $request)
    {
        $search = $this->request->get();
        if ($this->request->has('datetime')) {
            $search['datetime'] = $this->request->rangeTime();
        }

        $data = LeaveMessage::simple()->search($search)
            ->order('id desc')
            ->paginate($this->request->paginate());

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function create(Request $request)
    {

    }

    public function store(Request $request)
    {

    }

    public function show(Request $request)
    {

    }

    public function edit(Request $request)
    {

    }

    public function update(Request $request)
    {

    }

    public function destroy(Request $request)
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        LeaveMessage::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }
}
