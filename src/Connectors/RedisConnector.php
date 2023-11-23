<?php

namespace As247\Puller\Connectors;

use Illuminate\Contracts\Redis\Factory as Redis;
use As247\Puller\RedisPuller;

class RedisConnector implements ConnectorInterface
{
    /**
     * The Redis database instance.
     *
     * @var \Illuminate\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * Create a new Redis puller connector instance.
     *
     * @param  \Illuminate\Contracts\Redis\Factory  $redis
     * @return void
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Establish a puller connection.
     *
     * @param  array  $config
     * @return \As247\Puller\Contracts\Puller
     */
    public function connect(array $config)
    {
        return new RedisPuller(
            $this->redis,
            $config['table'],
            $config['remove_after'] ?? 60,
        );
    }
}
