<?php

namespace As247\Puller\Events;

use As247\Puller\Puller;

class MessagesPulled
{
    public $messages;
    public $channel;
    public $token;
    public $size;

    public function __construct($messages, $channel, $token, $size)
    {
        $this->messages = $messages;
        $this->channel = $channel;
        $this->token = $token;
        $this->size = $size;
    }
}
