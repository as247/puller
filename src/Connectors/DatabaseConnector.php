<?php

namespace As247\Puller\Connectors;

use Illuminate\Database\ConnectionResolverInterface;
use As247\Puller\DatabasePuller;

class DatabaseConnector
{
    /**
     * Database connections.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $connections;

    /**
     * Create a new connector instance.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connections
     * @return void
     */
    public function __construct(ConnectionResolverInterface $connections)
    {
        $this->connections = $connections;
    }

    /**
     * Establish a puller connection.
     *
     * @param  array  $config
     * @return \As247\Puller\Contracts\Puller
     */
    public function connect(array $config)
    {
        return new DatabasePuller(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
        );
    }
}
