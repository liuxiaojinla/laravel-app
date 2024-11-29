<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers;

use App\Http\Api\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Translation\Translator;
use Xin\Hint\Facades\Hint;
use Xin\Support\Reflect;

class LanguageController extends Controller
{

    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index()
    {
        /** @var Translator $translator */
        $translator = $this->app['translator'];
        $translator->load('*', '*', 'en');
        $translator->load('*', '*', 'zh_CN');
        $languages = Reflect::getPropertyValue($translator, 'loaded')['*']['*'];
        return Hint::result($languages);
    }

}
