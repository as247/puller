<?php

namespace As247\Puller;
use Closure;
use InvalidArgumentException;

/**
 * @mixin \As247\Puller\Contracts\Puller
 */
class PullerManager implements \As247\Puller\Contracts\Factory
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved puller connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The array of resolved puller connectors.
     *
     * @var array
     */
    protected $connectors = [];

    /**
     * Create a new puller manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Determine if the driver is connected.
     *
     * @param  string|null  $name
     * @return bool
     */
    public function connected($name = null)
    {
        return isset($this->connections[$name ?: $this->getDefaultDriver()]);
    }

    /**
     * Resolve a puller connection instance.
     *
     * @param  string|null  $name
     * @return \As247\Puller\Contracts\Puller
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        // If the connection has not been resolved yet we will resolve it now as all
        // of the connections are resolved when they are actually needed so we do
        // not make any unnecessary connection to the various puller end-points.
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->resolve($name);

            $this->connections[$name]->setContainer($this->app);
        }

        return $this->connections[$name];
    }

    /**
     * Resolve a puller connection.
     *
     * @param  string  $name
     * @return \As247\Puller\Contracts\Puller
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("The [{$name}] puller connection has not been configured.");
        }

        return $this->getConnector($config['driver'])
            ->connect($config)
            ->setConnectionName($name)
            ->setConfig($config);
    }

    /**
     * Get the connector for a given driver.
     *
     * @param  string  $driver
     * @return \As247\Puller\Connectors\ConnectorInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function getConnector($driver)
    {
        if (! isset($this->connectors[$driver])) {
            throw new InvalidArgumentException("No connector for [$driver].");
        }

        return call_user_func($this->connectors[$driver]);
    }

    /**
     * Add a puller connection resolver.
     *
     * @param  string  $driver
     * @param  \Closure  $resolver
     * @return void
     */
    public function extend($driver, Closure $resolver)
    {
        $this->addConnector($driver, $resolver);
    }

    /**
     * Add a puller connection resolver.
     *
     * @param  string  $driver
     * @param  \Closure  $resolver
     * @return void
     */
    public function addConnector($driver, Closure $resolver)
    {
        $this->connectors[$driver] = $resolver;
    }

    /**
     * Get the puller connection configuration.
     *
     * @param  string  $name
     * @return array|null
     */
    protected function getConfig($name)
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app['config']["puller.connections.{$name}"];
        }

        return ['driver' => 'null'];
    }

    /**
     * Get the name of the default puller connection.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['puller.default'];
    }

    /**
     * Set the name of the default puller connection.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['puller.default'] = $name;
    }

    /**
     * Get the full name for the given connection.
     *
     * @param  string|null  $connection
     * @return string
     */
    public function getName($connection = null)
    {
        return $connection ?: $this->getDefaultDriver();
    }

    /**
     * Get the application instance used by the manager.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Set the application instance used by the manager.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        foreach ($this->connections as $connection) {
            $connection->setContainer($app);
        }

        return $this;
    }

    /**
     * Dynamically pass calls to the default connection.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
