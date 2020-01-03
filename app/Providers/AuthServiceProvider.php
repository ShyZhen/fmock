<?php

namespace App\Providers;

use Carbon\Carbon;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // passport 认证相关路由 替换快捷创建方式不再需要路由
        // Passport::routes();
        // Passport::tokensExpireIn(Carbon::now()->addDays(15));
        Passport::personalAccessTokensExpireIn(now()->addDays(8));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
    }
}
