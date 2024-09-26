<?php

namespace app;

use Requtize\QueryBuilder\Connection;
use Requtize\QueryBuilder\ConnectionAdapters\PdoBridge;
use Requtize\QueryBuilder\QueryBuilder\QueryBuilder;
use Requtize\QueryBuilder\QueryBuilder\QueryBuilderFactory;

class DatabaseHelper
{
    const DB_HOST = 'localhost';
    const DB_PORT = '3306';
    const DB_NAME = 'queue-reminder';
    const DB_USER = 'root';
    const DB_PASSWORD = '';
    const DB_CHARSET = 'utf8mb4';
    public Connection $connection;

    public function __construct()
    {
        $dsn = 'mysql:host=' . self::DB_HOST . ';port=' . self::DB_PORT . ';dbname=' . self::DB_NAME . ';charset=' . self::DB_CHARSET;

        $pdo = new \PDO($dsn, self::DB_USER, self::DB_PASSWORD);

        $this->connection = new Connection(new PdoBridge($pdo));
    }

    public function getUsers()
    {
        $queryBuilder = new QueryBuilder($this->connection);
        return $queryBuilder->from('users')->all();
    }

    public function getTasks()
    {
        $queryBuilder = new QueryBuilder($this->connection);
        return $queryBuilder->from('tasks')->all();
    }

    public function getFirstTask()
    {
        $queryBuilder = new QueryBuilder($this->connection);
        return $queryBuilder->from('tasks')->first();
    }

    public function getActiveUser($task_id)
    {
        $queryBuilder = new QueryBuilder($this->connection);
        return $queryBuilder->from('user_tasks')
            ->select(['users.telegram_id', 'users.name', 'user_id'])
            ->leftJoin('users', 'users.id', '=', $queryBuilder->raw('user_tasks.user_id'))
            ->where('task_id', '=', $task_id)
            ->where('status', '=', 1)
            ->first();
    }

    public function isTaskExists($user_id, $task_id): bool
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $task = $queryBuilder->from('user_tasks')
            ->where('user_id', '=', $user_id)
            ->where('task_id', '=', $task_id)
            ->first();

        if ($task == null) {
            return false;
        }
        return true;
    }

    public function insert(string $table, $data): void
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder->insert($data, $table);
    }

    public function nextUser($current_user_id)
    {
        $queryBuilder = $this->getPdoConnection();
        $sql = <<<SQL
UPDATE user_tasks SET status=0 WHERE user_id={$current_user_id}
SQL;
        $queryBuilder->exec($sql);

        $next_user_id = $current_user_id + 1;
        $queryBuilder = new QueryBuilder($this->connection);
        $next_user = $queryBuilder->from('user_tasks')->where('user_id', '=', $next_user_id)->first();
        $queryBuilder = $this->getPdoConnection();
        if ($next_user == null) {
            $sql = <<<SQL
UPDATE user_tasks SET status=1 WHERE user_id=1
SQL;
        } else {
            $sql = <<<SQL
UPDATE user_tasks SET status=1 WHERE user_id={$next_user_id}
SQL;
        }
        $queryBuilder->exec($sql);
    }

    public function getPdoConnection()
    {
        $dsn = 'mysql:host=' . self::DB_HOST . ';port=' . self::DB_PORT . ';dbname=' . self::DB_NAME . ';charset=' . self::DB_CHARSET;

        return new \PDO($dsn, self::DB_USER, self::DB_PASSWORD);

    }
}