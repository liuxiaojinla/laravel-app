<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Validator;
use Symfony\Component\Routing\RequestContext;
use Xin\LaravelFortify\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 验证器
        $this->resolvingValidation();
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
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 数据模型映射
        Relation::enforceMorphMap(config('database.morph_mapping'));

        // 加载站点配置到系统配置中
        //        $this->app['setting']->loadToSystemConfig();

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
                    'data'         => $data->items(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
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
