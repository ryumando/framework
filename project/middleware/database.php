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
        #       'user' => self::env('MYSQL_USER'),
        #       'password' => self::env('MYSQL_PASSWORD'),
        #       'dbname' => self::env('MYSQL_DBNAME'),
        #       'host' => self::env('MYSQL_HOST'),
        #   ],
        #   'postgres' => [
        #       'driver' => 'postgres',
        #       'user' => self::env('POSTGRES_USER'),
        #       'password' => self::env('POSTGRES_PASSWORD'),
        #       'dbname' => self::env('POSTGRES_DBNAME'),
        #       'host' => self::env('POSTGRES_HOST'),
        #   ]
        ];
        parent::__construct($key);
    }
}