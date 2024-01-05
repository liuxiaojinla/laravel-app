<?php

namespace App\Core\Module;

use App\Core\WithConfig;
use App\Core\WithContainer;
use App\Http\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @property-read Application $container
 */
class ModuleManager
{
    use ModuleBootstrapTrait, WithConfig, WithContainer;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string|null
     */
    protected ?string $module = null;

    /**
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * @var array
     */
    protected array $needMergeMiddlewareGroups = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge_recursive($this->config, self::makeDefaultConfig(), $config);
        $this->initialize();
    }

    /**
     * 初始化模块信息
     * @return void
     */
    protected function initialize()
    {
        // 路由组信息
        $this->needMergeMiddlewareGroups = [];

        // 加载路由文件
        $modulesConfig = $this->getModulesConfig();
        foreach ($modulesConfig as $moduleName => &$moduleConfig) {
            $moduleConfig['path'] = $moduleConfig['path'] ?? app_path(Str::studly($moduleName));
            $moduleConfig['route'] = $this->optimizeRouteConfig($moduleConfig['route'] ?? [], $moduleName);
            $moduleConfig['view'] = $this->optimizeViewConfig($moduleConfig['view'] ?? [], $moduleConfig);

            // 检测中间件配置文件是否存在
            $middlewareFile = $moduleConfig['middleware_file'] ?? $moduleConfig['path'] . DIRECTORY_SEPARATOR . "middleware.php";
            if (file_exists($middlewareFile)) {
                $moduleConfig['middleware_file'] = $middlewareFile;
                $middlewareConfig = require_once $middlewareFile;
                $this->needMergeMiddlewareGroups[$moduleName] = $middlewareConfig;
            }

            // 拼接路由配置文件
            $moduleConfig['route_path'] = $moduleConfig['route_path'] ?? $moduleConfig['path']
            . DIRECTORY_SEPARATOR . 'routes'
            . DIRECTORY_SEPARATOR . 'index.php';
        }
        unset($moduleConfig);

        $this->config['modules'] = $modulesConfig;
    }

    /**
     * 优化路由配置信息
     * @param array $routeConfig
     * @param string $moduleName
     * @return array
     */
    protected function optimizeRouteConfig(array $routeConfig, $moduleName)
    {
        $routeConfig['prefix'] = $routeConfig['prefix'] ?? $moduleName;
        return $routeConfig;
    }

    /**
     * 优化视图配置
     * @param array $viewConfig
     * @param array $moduleConfig
     * @return array
     */
    protected function optimizeViewConfig(array $viewConfig, array $moduleConfig)
    {
        $viewConfig = array_replace_recursive([
            'paths' => [
                implode(DIRECTORY_SEPARATOR, [
                    $moduleConfig['path'], "resources", 'views',
                ]),

            ],
        ], $viewConfig);

        return $viewConfig;
    }

    /**
     * 解析模块
     * @param string $requestPath
     * @return array
     */
    public function parse(string $requestPath): array
    {
        $module = $this->getDefault();
        $modulePath = $requestPath;

        if ($index = strpos($requestPath, '/')) {
            $module = substr($requestPath, 0, $index);
            if (in_array($module, $this->getModuleNames())) {
                $modulePath = substr($requestPath, $index + 1);
            } else {
                $module = $this->getDefault();
            }
        } else {
            if (in_array($requestPath, $this->getModuleNames())) {
                $module = $requestPath;
                $modulePath = '';
            }
        }

        $this->module = $module;
        $this->path = $modulePath;

        return [$module, $modulePath];
    }

    /**
     * @param Request $request
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function run(Request $request)
    {
        // 初始化
        [$module, $modulePath] = $this->parse($request->path());
        $this->registerRequestMacros($module, $modulePath);

        // 模块注册
        $this->moduleOnRegister();

        // 注册视图
        $this->registerViews();

        // 更新内核中间件配置信息
        $this->updateModuleMiddlewares();

        // 注册路由文件
        $this->registerModuleRoutes();

        // 启动模块
        $this->moduleOnBoot();
    }

    /**
     * 注册请求器相关宏操作
     * @return void
     */
    protected function registerRequestMacros($module, $modulePath)
    {
        Request::macro('setPathInfo', function ($pathInfo) {
            // /** @var Request $this */
            $this->pathInfo = $pathInfo;
        });

        Request::macro('module', function () use ($module) {
            return $module;
        });

        Request::macro('modulePath', function () use ($modulePath) {
            return $modulePath;
        });
    }

    /**
     * @return void
     */
    protected function registerViews()
    {
        $moduleConfig = $this->getModuleConfig($this->getModule());
        Config::set('view', array_replace_recursive(
            Config::get('view'), $moduleConfig['view']
        ));
    }

    /**
     * 更新内核中间件配置信息
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    protected function updateModuleMiddlewares()
    {
        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get(KernelContract::class);
        $kernelMiddlewareProperty = new \ReflectionProperty($kernel, 'middlewareGroups');
        $originalMiddlewareGroups = $kernelMiddlewareProperty->getValue($kernel);
        $kernelMiddlewareProperty->setValue($kernel, array_merge_recursive(
            $originalMiddlewareGroups, $this->needMergeMiddlewareGroups
        ));
        $kernelSyncMiddlewareToRouterMethod = new \ReflectionMethod($kernel, 'syncMiddlewareToRouter');
        $kernelSyncMiddlewareToRouterMethod->invoke($kernel);
    }

    /**
     * 注册路由信息
     * @return void
     */
    protected function registerModuleRoutes()
    {
        foreach ($this->getModulesConfig() as $moduleConfig) {
            if (!file_exists($moduleConfig['route_path'])) {
                throw new \RuntimeException("路由配置文件不存在[{$moduleConfig['route_path']}]");
            }
            Route::group($moduleConfig['route'], $moduleConfig['route_path']);
        }
    }


    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->getConfig('defaults.module', 'web');
    }

    /**
     * @return array
     */
    public function getModulesConfig(): array
    {
        return $this->getConfig('modules', []);
    }

    /**
     * @param string $module
     * @return array
     */
    public function getModuleConfig(string $module): array
    {
        return $this->getConfig('modules.' . $module, null);
    }

    /**
     * @return array
     */
    public function getModuleNames(): array
    {
        return array_keys($this->getModulesConfig());
    }

    /**
     * @return string|null
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public static function makeDefaultConfig()
    {
        return [
            'defaults' => [
                'module' => 'web',
            ],

            // 模块列表
            'modules' => [
                'api' => [
//                    'route' => [
//                        'middleware' => 'api',
//                    ],
                    'exceptionShouldReturnJson' => true,
                ],
                'web' => [
                    'path' => app_path('Http'),
//                    'route' => [
//                        'prefix' => '',
//                        'middleware' => 'web',
//                    ],
//                    'route_path' => base_path('routes/web.php'),
                    'view' => [
                        'paths' => [
                            resource_path('views'),
                        ],
                    ],
                    'exceptionShouldReturnJson' => true,
                ],
                'admin' => [
//                    'route' => [
//                        'middleware' => 'admin',
//                    ],
                    'exceptionShouldReturnJson' => true,
                ],
                'notify' => [
//                    'route' => [
////            'middleware' => 'notify',
//                    ],
                    'exceptionShouldReturnJson' => true,
                ],
            ],
        ];
    }
}
