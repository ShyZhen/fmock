<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\BaseService\ElasticSearchService;

class PostObserver
{
    private $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }

    /**
     * 在创建Post的时候自动加人到ES中
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param Post $post
     * @throws \Exception
     */
    public function created(Post $post)
    {
        /*
        $body = [
            'title' => $post->title,
            'content' => $post->content,
            'user_id' => $post->user_id
        ];

        $this->elasticSearchService->createDoc(env('ES_INDEX'), $post->id, $body);
        */
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  Post  $post
     * @return void
     */
    public function updated(Post $post)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  Post  $post
     * @return void
     */
    public function deleted(Post $post)
    {
        //
    }

    /**
     * Handle the post "restored" event.
     *
     * @param  Post  $post
     * @return void
     */
    public function restored(Post $post)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  Post  $post
     * @return void
     */
    public function forceDeleted(Post $post)
    {
        //
    }
}
