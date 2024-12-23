<?php

namespace App\Admin\Controllers\Auth;

use App\Admin\Controller;
use App\Admin\Services\AdminService;
use Illuminate\Foundation\Application;
use Xin\Hint\Facades\Hint;

class ProfileController extends Controller
{
    /**
     * @var AdminService
     */
    protected $adminService;

    /**
     * @param Application $app
     * @param AdminService $adminService
     */
    public function __construct(Application $app, AdminService $adminService)
    {
        parent::__construct($app);

        $this->adminService = $adminService;
    }

    /**
     * 获取当前登录用户信息
     * @return mixed
     */
    public function info()
    {
        $userId = $this->auth->id();
        $user = $this->adminService->get($userId);

        return Hint::result($user);
    }

    /**
     * 更新当前登录用户信息
     * @return mixed
     */
    public function update()
    {
        $userId = $this->auth->id();

        $data = $this->request->only(['nickname', 'gender']);
        $user = $this->adminService->update($userId, $data);

        return Hint::result($user);
    }
}
