<?php

namespace Webman\Database;

use Illuminate\Database\DatabaseManager as BaseDatabaseManager;
use Throwable;
use Webman\Context;
use Webman\Coroutine\Pool;

/**
 * Class DatabaseManager
 */
class DatabaseManager extends BaseDatabaseManager
{

    /**
     * @var Pool[]
     */
    protected static array $pools = [];

    /**
     * Get connection
     *
     * @param $name
     * @return mixed
     * @throws Throwable
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();
        [$database, $type] = $this->parseConnectionName($name);
        
        $key = "database.connections.$name";
        $connection = Context::get($key);
        if (!$connection) {
            if (!isset(static::$pools[$name])) {
                $poolConfig = $this->app['config']['database.connections'][$name]['pool'] ?? [];
                $pool = new Pool($poolConfig['max_connections'] ?? 6, $poolConfig);
                $pool->setConnectionCreator(function () use ($database, $type) {
                    return $this->configure($this->makeConnection($database), $type);
                });
                $pool->setConnectionCloser(function ($connection) {
                    $this->closeAndFreeConnection($connection);
                });
                $pool->setHeartbeatChecker(function ($connection) {
                    $connection->select('select 1');
                });
                static::$pools[$name] = $pool;
            }
            try {
                $connection = static::$pools[$name]->get();
                Context::set($key, $connection);
            } finally {
                Context::onDestroy(function () use ($connection, $name) {
                    try {
                        $connection && static::$pools[$name]->put($connection);
                    } catch (Throwable) {
                        // ignore
                    }
                });
            }
        }
        return $connection;
    }

    /**
     * Close connection.
     *
     * @param $connection
     * @return void
     */
    protected function closeAndFreeConnection($connection): void
    {
        $connection->disconnect();
        $clearProperties = function () {
            $this->queryGrammar = null;
        };
        $clearProperties->call($connection);
    }

}
