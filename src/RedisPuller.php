<?php

namespace As247\Puller;

use Illuminate\Contracts\Redis\Factory as Redis;
class RedisPuller extends Puller
{
    protected $redis;
    protected $table;
    public function __construct(Redis $redis,$table, $removeAfter = 60)
    {
        $this->redis = $redis;
        $this->table = $table;
        $this->removeAfter = $removeAfter;
    }

    protected function store(Message $message)
    {
        $this->redis->rpush($this->table, $message->toJson());
    }

    protected function fetch($channel, $token, $size)
    {
        // TODO: Implement fetch() method.
    }

    protected function purge()
    {
        // TODO: Implement purge() method.
    }

    protected function lastToken($channel)
    {
        // TODO: Implement lastToken() method.
    }
}
