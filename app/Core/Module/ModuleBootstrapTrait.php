<?php

namespace App\Core\Module;

use Illuminate\Support\Str;

trait ModuleBootstrapTrait
{
    /**
     * @var null
     */
    protected $moduleBootstrap = null;

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
        if ($this->moduleBootstrap && is_a($this->moduleBootstrap, $moduleBootstrapClass)) {
            return $this->moduleBootstrap;
        }

        if (!class_exists($moduleBootstrapClass)) {
            return null;
        }

        return $this->moduleBootstrap = $this->container->make($moduleBootstrapClass);
    }
}
