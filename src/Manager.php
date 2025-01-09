<?php

namespace Webman\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;

class Manager extends \Illuminate\Database\Capsule\Manager
{
    /**
     * Build the database manager instance.
     *
     * @return void
     */
    protected function setupManager()
    {
        $factory = new ConnectionFactory($this->container);
        $this->manager = new DatabaseManager($this->container, $factory);
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }
}