<?php

namespace App\Http\Admin\Controllers\System;

use App\Http\Admin\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\SimpleCache\InvalidArgumentException;
use Xin\Hint\Facades\Hint;
use Xin\Setting\Contracts\Factory as SettingFactoryContract;
use Xin\Setting\Exceptions\NotFountSettingItemException;
use Xin\Setting\SettingManager;

class SettingController extends Controller
{
    /**
     * @var SettingManager
     */
    private $settingManager;

    /**
     * @param SettingFactoryContract $factory
     */
    public function __construct(SettingFactoryContract $factory)
    {
        $this->settingManager = $factory;
    }

    /**
     * 数据列表
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = $this->settingManager->paginate($search);
        if ($request->ajax()){
            return Hint::result($data);
        }

        return Hint::result($data);

        return view('setting.index', [
            'data' => $data,
        ]);
    }

    /**
     * 数据创建表单
     * @param Request $request
     * @return View
     * @throws NotFountSettingItemException
     */
    public function create(Request $request)
    {
        $key = $request->input('key', '');
        $copy = 0;
        $info = null;

        if (!empty($key)) {
            $copy = 1;
            $info = $this->settingManager->get($key);
        }

        return view('setting.edit', [
            'copy' => $copy,
            'info' => $info,
        ]);
    }

    /**
     * 数据创建
     * @param Request $request
     * @return Response
     */
    public function put(Request $request)
    {
        $key = $request->input('name');
        $data = $request->except(['name']);
        $data['title'] = now();
        $data['value'] = now();

        $info = $this->settingManager->put($key, $data);

        return Hint::result($info);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return View
     * @throws NotFountSettingItemException
     */
    public function show(Request $request)
    {
        $key = $request->validString('key');

        $info = $this->settingManager->get($key);

        return view('setting.show', [
            'info' => $info,
        ]);
    }

    /**
     * 数据更新表单
     * @param Request $request
     * @return View|Response
     */
    public function edit(Request $request)
    {
        $key = $request->validString('key');

        try {
            $info = $this->settingManager->get($key);
        } catch (NotFountSettingItemException $e) {
            return Hint::error($e->getMessage());
        }

        return view('agreement.edit', [
            'info' => $info,
        ]);
    }

    /**
     * 数据删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $keys = $request->validIds('keys', 'strval');

        $result = $this->settingManager->deletes($keys);

        return Hint::result($result);
    }

    /**
     * 更新站点配置
     * @param Request $request
     * @return Response
     * @throws InvalidArgumentException
     */
    public function set(Request $request)
    {
        $result = $this->settingManager->sets([
            '1704955431' => 1,
            'user_recharge_status1704955460' => 2,
            'user_recharge_status1704955473' => 3,
            'user_recharge_status1704955550' => 4,
        ]);

        $this->settingManager->clearCache();

        return Hint::result($result);
    }
}
