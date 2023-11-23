<?php

namespace As247\Puller\Events;

class MessagePushed
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}
