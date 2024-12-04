<?php

namespace App\Admin\Controllers\System;

use App\Admin\Controller;
use App\Exceptions\Error;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Psr\SimpleCache\InvalidArgumentException;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;
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
     * @param Application $app
     * @param SettingFactoryContract $factory
     */
    public function __construct(Application $app, SettingFactoryContract $factory)
    {
        parent::__construct($app);
        $this->settingManager = $factory;
    }

    /**
     * 数据列表
     *
     * @return View
     */
    public function index()
    {
        $search = $this->request->query();

        $data = $this->settingManager->paginate($search);

        return Hint::result($data);
    }

    /**
     * 数据展示
     *
     * @return View
     * @throws NotFountSettingItemException
     */
    public function info()
    {
        $key = $this->request->validString('key');

        $info = $this->settingManager->info($key);

        return Hint::result($info);
    }

    /**
     * 数据创建
     *
     * @return Response
     */
    public function upsert()
    {
        $key = $this->request->validString('name');
        $data = $this->request->except(['name']);

        $info = $this->settingManager->upsert($key, $data);

        return Hint::result($info);
    }

    /**
     * 数据删除
     *
     * @return Response
     */
    public function delete()
    {
        $keys = $this->request->validIds('keys', 'strval');

        $result = $this->settingManager->deletes($keys);

        return Hint::result($result);
    }

    /**
     * 更新站点配置
     * @return Response
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function set()
    {
        $settings = $this->request->input('settings');
        if (empty($settings)) {
            throw Error::validationException("提交的数据不合法！");
        }

        $result = $this->settingManager->setMultiple($settings);

        $this->settingManager->clearCache();

        return Hint::result($result);
    }
}
