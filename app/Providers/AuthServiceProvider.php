<?php

namespace App\Providers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

        } else {
            $this->registerPolicies();
            $this->bootInWebServer();
        }
    }


    /**
     * @return void
     * @throws BindingResolutionException
     */
    protected function bootInWebServer()
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Auth::resolved(function (AuthManager $auth) {
            $auth->forgetGuards();
            $auth->viaRequest('sanctum', function ($request) {
                return $this->app->make(UserService::class)->get(1);
                return User::query()->first();
            });
        });
    }
}
