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
        #       'user' => 'mysql_user',
        #       'password' => 'mysql_password',
        #       'dbname' => 'mysql_db',
        #       'host' => 'mysql',
        #   ],
        #   'postgres' => [
        #       'driver' => 'postgres',
        #       'user' => 'postgres_user',
        #       'password' => 'postgres_password',
        #       'dbname' => 'postgres_db',
        #       'host' => 'postgres',
        #   ],
        ];
        parent::__construct($key);
    }
}