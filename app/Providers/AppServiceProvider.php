<?php

namespace App\Providers;

use App\Core\RequestContext;
use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 验证器
        $this->resolvingValidation();

        // 注册模块加载器
        $this->registerModuleManager();
    }

    /**
     * @return void
     */
    protected function resolvingValidation(): void
    {
        $this->app->resolving(ValidationFactory::class, function (ValidationFactory $factory) {
            $factory->resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
                $validator = new Validator($translator, $data, $rules, $messages, $customAttributes);
                $validator->setException(ValidationException::class);

                return $validator;
            });
        });
    }

    /**
     * 注册模块加载器
     * @return void
     */
    protected function registerModuleManager(): void
    {
//        $moduleManager = new ModuleManager(
//            config('module', [])
//        );
//        $moduleManager->setContainer($this->app);
//        $this->app->instance('module', $moduleManager);
//        $this->app->alias('module', ModuleManager::class);
//
//        // 加载模块路
//        $this->app->booted(function () use ($moduleManager) {
//            $moduleManager->run($this->app['request']);
//        });
    }

    protected function registerRequestMacros()
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 数据模型映射
        Relation::enforceMorphMap(config('database.morph_mapping'));

        if ($this->app->runningInConsole()) {
            $this->bootInConsole();
        } else {
            $this->bootInWebServer();
        }
    }

    private function bootInConsole()
    {
    }

    private function bootInWebServer(): void
    {
        // 增加Hint数据处理器
        $this->app['hint']->hint('api')->setDataPreprocessor(function ($data) {
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                return [
                    'current_page' => $data->currentPage(),
                    'data' => $data->items(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ];
            }

            return $data;
        });

        // 注册Request上下文
        $this->registerRequestContext();
    }

    /**
     * 注册Request上下文
     * @return void
     */
    protected function registerRequestContext(): void
    {
        $this->app->singleton(RequestContext::class, function (Request $request) {
            return new RequestContext($request);
        });
    }
}
