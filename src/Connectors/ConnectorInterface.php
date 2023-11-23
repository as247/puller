<?php

namespace As247\Puller\Connectors;

interface ConnectorInterface
{
    /**
     * Establish a puller connection.
     *
     * @param  array  $config
     * @return \As247\Puller\Contracts\Puller
     */
    public function connect(array $config);
}
