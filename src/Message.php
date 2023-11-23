<?php

namespace As247\Puller;

class Message
{
    public $id;
    public $token;
    public $channel;
    public $payload;
    public $created_at;
    public $updated_at;

    public function __construct($message=null)
    {
        if(!$message){
            return;
        }
        $this->id=$message->id;
        $this->token=$message->token;
        $this->channel=$message->channel;
        $this->payload=json_decode($message->payload);
        $this->created_at=$message->created_at;
        $this->updated_at=$message->updated_at;
    }

    function toDatabase(){
        return [
            'token'=>$this->token,
            'channel'=>$this->channel,
            'payload'=>$this->payload,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
    function toJson(){
        return json_encode($this->toDatabase());
    }
}
