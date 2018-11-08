<?php

namespace App\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Events\AccessTokenCreated;

class RevokeOldTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AccessTokenCreated $event
     *
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        // 清除失效token监听事件
        DB::table('oauth_access_tokens')
            ->where([
                ['id', '<>', $event->tokenId],
                ['client_id', '=', $event->clientId],
                ['user_id', '=', $event->userId],
                ['created_at', '<', Carbon::now()],
                ['revoked', '=', 0],
            ])->delete();
    }
}
