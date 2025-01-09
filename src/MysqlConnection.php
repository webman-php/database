<?php

namespace Webman\Database;

use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

class MysqlConnection extends BaseMySqlConnection
{
    /**
     * Disconnect。
     *
     * @return void
     */
    public function disconnect()
    {
        parent::disconnect();
        // Set $this->queryGrammar to null to avoid memory leaks.
        $this->queryGrammar = null;
    }

    /**
     * Reconnect.
     *
     * @return void
     */
    public function reconnect()
    {
        $result = parent::reconnect();
        if (!$this->queryGrammar) {
            $this->useDefaultQueryGrammar();
        }
        return $result;
    }

    public function __destruct()
    {
        echo "__destruct\n";
    }
}