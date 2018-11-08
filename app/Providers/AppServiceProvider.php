<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 设置全局语言环境
        $locale = request()->get('locale') ? request()->get('locale') : 'zh-CN';
        $locale = in_array($locale, [Config::get('app.locale'), Config::get('app.fallback_locale')]) ? $locale : 'zh-CN';
        App::setLocale($locale);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
