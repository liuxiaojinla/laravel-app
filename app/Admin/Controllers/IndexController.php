<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Response;
use Qiniu\Http\Request;
use Xin\Hint\Facades\Hint;
use Xin\Setting\Contracts\Factory as SettingFactory;
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
        $data = event('QuickSearch', $request->keywordsSql('global_keywords'));
        if (!empty($data)) {
            $data = array_filter(Arr::flatten($data, 1));
        }

        return Hint::result($data);
    }

    /**
     * @param SettingFactory $factory
     * @return Response
     */
    public function config(SettingFactory $factory)
    {
        $data = $factory->loadOnPublic();
        return Hint::result($data);
    }

}
