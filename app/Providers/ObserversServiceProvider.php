<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\Answer;
use App\Observers\PostObserver;
use App\Observers\AnswerObserver;
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * 所有的observe需在此进行注册
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if (env('ESToObserver')) {
            Post::observe(PostObserver::class);
            Answer::observe(AnswerObserver::class);
        }
    }
}
