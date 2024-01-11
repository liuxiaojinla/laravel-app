<?php

namespace App\Http\Admin\Controllers\System;

use App\Http\Admin\Controllers\Controller;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;
use Xin\Setting\Contracts\Factory as SettingFactoryContract;
use Xin\Setting\SettingManager;

class SettingController extends Controller
{
    /**
     * @var SettingManager
     */
    private SettingFactoryContract $settingManager;

    public function __construct(SettingFactoryContract $factory)
    {
        $this->settingManager = $factory;
    }

    public function lists()
    {
        $data = $this->settingManager->paginate();

        return Hint::result($data);
    }

    public function read(Request $request)
    {
        $key = $request->validString('key');

        $info = $this->settingManager->get($key);

        return Hint::result($info);
    }

    public function put(Request $request)
    {
        $key = $request->input('name');
        $data = $request->except(['name']);
        $data['title'] = now();
        $data['value'] = now();

        $info = $this->settingManager->put($key, $data);

        return Hint::result($info);
    }

    public function delete(Request $request)
    {
        $keys = $request->validIds('keys', 'strval');

        $result = $this->settingManager->deletes($keys);

        return Hint::result($result);
    }


    public function set(Request $request)
    {
        $result = $this->settingManager->sets([
            '1704955431' => 1,
            'user_recharge_status1704955460' => 2,
            'user_recharge_status1704955473' => 3,
            'user_recharge_status1704955550' => 4,
        ]);

        return Hint::result($result);
    }
}
