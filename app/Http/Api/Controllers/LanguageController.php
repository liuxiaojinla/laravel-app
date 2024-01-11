<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller;

use app\api\Controller;
use Xin\Hint\Facades\Hint;

class LanguageController extends Controller
{

    /**
     * @return \think\Response
     */
    public function index()
    {
        return Hint::result([
            'en-us' => require_once base_path('lang') . 'en-us.php',
            'zh-cn' => require_once base_path('lang') . 'zh-cn.php',
        ]);
    }

}
