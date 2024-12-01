<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use Qiniu\Http\Request;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

class IndexController extends Controller
{

    /**
     * @return string
     */
    public function index()
    {
        return Hint::success("Admin API.");
    }

    /**
     * 快速搜索
     *
     * @return string
     */
    public function quickSearch(Request $request)
    {
        $data = adv_event('QuickSearch', $request->keywordsSql('global_keywords'));
        if (!empty($data)) {
            $data = array_filter(Arr::flatten($data, 1));
        }

        $this->assign('data', $data);

        return view('quick_search', [
            'data' => $data,
        ]);
    }

}
