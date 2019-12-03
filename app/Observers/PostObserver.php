<?php

namespace App\Observers;

use App\Models\Post;
use App\Repositories\Eloquent\UserRepository;
use App\Library\ElasticSearch\PostElasticSearch;

class PostObserver
{
    private $userRepository;

    private $postElasticSearch;

    public function __construct(PostElasticSearch $postElasticSearch, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->postElasticSearch = $postElasticSearch;
    }

    /**
     * 在创建Post的时候自动加人到ES中
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param Post $post
     *
     * @throws \Exception
     */
    public function created(Post $post)
    {
        $user = $this->userRepository->find($post->user_id, 'name');

        $body = [
            'title' => $post->title,
            'content' => $post->content,
            'username' => $user ? $user->name : __('app.anonymous'),
        ];

        $this->postElasticSearch->createDoc($post->id, $body);
    }

    /**
     * Handle the post "updated" event.
     *
     * @param Post $post
     *
     * @return void
     */
    public function updated(Post $post)
    {
        //
        $user = $this->userRepository->find($post->user_id, 'name');

        $body = [
            'title' => $post->title,
            'content' => $post->content,
            'username' => $user ? $user->name : __('app.anonymous'),
        ];

        $this->postElasticSearch->updateDoc($post->id, $body);
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param Post $post
     *
     * @return void
     */
    public function deleted(Post $post)
    {
        //
        $this->postElasticSearch->deleteDoc($post->id);
    }

    /**
     * Handle the post "restored" event.
     *
     * @param Post $post
     *
     * @return void
     */
    public function restored(Post $post)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param Post $post
     *
     * @return void
     */
    public function forceDeleted(Post $post)
    {
        //
    }
}
