<?php
declare (strict_types=1);

namespace App\Http;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

/**
 * 控制器基础类
 *
 * @property-read Application $app
 * @property-read Request $request
 * @property-read StatefulGuard $auth
 */
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 应用实例
     *
     * @var Application
     */
    protected $app;

    /**
     * Request实例
     *
     * @var Request
     */
    protected $request;

    /**
     * 控制器中间件
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * @var StatefulGuard
     */
    protected $auth;

    /**
     * 构造方法
     *
     * @access public
     * @param Application $app 应用对象
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->auth = $this->app['auth']->guard();

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

}
