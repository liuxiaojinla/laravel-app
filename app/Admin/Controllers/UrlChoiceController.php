<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class UrlChoiceController extends Controller
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->initConfig();

        $module = trim($request->query('_module', ''));
        $subModule = (int)$request->query('_submodule', -1);

        if (empty($this->data)) {
            return Hint::result([
                'config' => $this->data,
            ]);
        }

        if (empty($module) || !isset($this->data[$module])) {
            $module = key($this->data);
        }

        if (empty($this->data[$module]['statics']) && $subModule == -1) {
            $subModule = 0;
        }

        if ($subModule != -1) {
            if (!isset($this->data[$module]['resources'][$subModule])) {
                $subModule = key($this->data[$module]['resources']);
                $subModule = $subModule === null ? -1 : $subModule;
            }

            if ($subModule != -1) {
                $item = $this->data[$module]['resources'][$subModule];
                $data = $this->app->call($item['data']);
            }
        }

        return Hint::result([
            'data' => $data ?? [],
            'config' => $this->data,
            'module' => $module,
            'submodule' => $subModule,
        ]);
    }

    /**
     * 初始化配置信息
     */
    protected function initConfig()
    {
        $define = function ($module, $list, $options = []) {
            if (!isset($this->data[$module])) {
                $this->data[$module] = array_merge($options, [
                    'resources' => [],
                    'statics' => [],
                ]);
            }

            foreach ($list as $item) {
                $static = isset($item['static']) && (bool)$item['static'];
                unset($item['static']);
                if ($static) {
                    $this->data[$module]['statics'][] = $item;
                } else {
                    $this->data[$module]['resources'][] = $item;
                }
            }
        };

        event('URLChoiceInit', $define);
    }
}
