<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use Qiniu\Http\Request;
use Xin\Support\Arr;
use function App\Http\Admin\Controllers\adv_event;

class IndexController extends Controller
{

    /**
     * @return string
     */
    public function index()
    {
        return view('index');
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
