<?php

namespace App\Http\Admin\Controllers;

class UrlChoiceController extends Controller
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return string
     */
    public function index()
    {
        $this->initConfig();

        $module = $this->request->get('_module', '', 'trim');
        $subModule = $this->request->get('_submodule/d', -1);

        if (empty($this->data)) {
            $this->assign('config', $this->data);

            return $this->fetch();
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
                $data = $this->app->invoke($item['data']);
                $this->assign('data', $data);
            }
        }

        $this->assign('config', $this->data);
        $this->assign('module', $module);
        $this->assign('submodule', $subModule);

        return $this->fetch();
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

        adv_event('URLChoiceInit', $define);
    }
}
