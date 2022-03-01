<?php

namespace Middleware;

/**
 * データベースクラス
 */
class Database extends \Common\Core\Database
{

    /**
     * コンストラクタ
     *
     * @param string|null $key
     */
    public function __construct(string $key = null)
    {
        self::$databases = [
        #   'sqlite' => [
        #       'driver' => 'sqlite',
        #       'dbname' => ':memory:',
        #   ],
        #   'mysql' => [
        #       'driver' => 'mysql',
        #       'user' => self::config('database.mysql.user'),
        #       'password' => self::config('database.mysql.password'),
        #       'dbname' => self::config('database.mysql.dbname'),
        #       'host' => self::config('database.mysql.host'),
        #   ],
        #   'postgres' => [
        #       'driver' => 'postgres',
        #       'user' => self::config('database.postgres.user'),
        #       'password' => self::config('database.postgres.password'),
        #       'dbname' => self::config('database.postgres.dbname'),
        #       'host' => self::config('database.postgres.host'),
        #   ],
        ];
        parent::__construct($key);
    }
}