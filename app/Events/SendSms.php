<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SendSms
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    // email || mobile
    public $type;

    public $account;

    public $code;

    // register || password
    public $action;

    /**
     * Create a new event instance.
     *
     * @param $json
     *
     * @return void
     */
    public function __construct(string $json)
    {
        //
        $data = json_decode($json);
        $this->type = $data->type;
        $this->account = $data->account;
        $this->code = $data->code;
        $this->action = $data->action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
