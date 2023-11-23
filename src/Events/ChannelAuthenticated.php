<?php

namespace As247\Puller\Events;

class ChannelAuthenticated
{
    public $channel;
    public $token;

    public function __construct($channel, $token)
    {
        $this->channel = $channel;
        $this->token = $token;
    }
}
