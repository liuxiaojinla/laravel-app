<?php

namespace App\Services\Module;

use App\Http\Kernel;
use App\Services\Service;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @property-read Application $container
 */
class ModuleManager extends Service
{
    /**
     * @var string|null
     */
    protected ?string $module = null;

    /**
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * @var null
     */
    protected $moduleBootstarp = null;

    /**
     * @var array
     */
    protected array $needMergeMiddlewareGroups = [];

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

        // 加载模块路由信息
        $this->loadModuleRoutes();

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
     * 加载路由模块信息
     * @return void
     */
    protected function loadModuleRoutes()
    {
        // 路由组信息
        $this->needMergeMiddlewareGroups = [];

        // 加载路由文件
        $modulesConfig = $this->getModulesConfig();
        foreach ($modulesConfig as $moduleName => &$moduleConfig) {
            $moduleConfig['prefix'] = $moduleConfig['prefix'] ?? $moduleName;
            $moduleConfig['path'] = $moduleConfig['path'] ?? app_path(Str::studly($moduleName));

            // 检测中间件配置文件是否存在
            $middlewareFile = $moduleConfig['path'] . DIRECTORY_SEPARATOR . "middleware.php";
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
            Route::group($moduleConfig, $moduleConfig['route_path']);
        }
    }

    /**
     * 模块注册事件
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function moduleOnRegister()
    {
        $instance = $this->moduleBootstrapInstance();
        if ($instance && method_exists($instance, 'register')) {
            $this->container->call([$instance, 'register',]);
        }
    }

    /**
     * 模块启动事件
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function moduleOnBoot()
    {
        $instance = $this->moduleBootstrapInstance();
        if ($instance && method_exists($instance, 'boot')) {
            $this->container->call([$instance, 'boot',]);
        }
    }

    /**
     * 模块启动实例
     * @return mixed|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function moduleBootstrapInstance()
    {
        $moduleBootstrapClass = "\\App\\" . Str::studly($this->getModule()) . "\\ModuleBootstrap";
        if ($this->moduleBootstarp && is_a($this->moduleBootstarp, $moduleBootstrapClass)) {
            return $this->moduleBootstarp;
        }

        if (!class_exists($moduleBootstrapClass)) {
            return null;
        }

        return $this->moduleBootstarp = $this->container->make($moduleBootstrapClass);
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


}
