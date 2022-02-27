<?php

namespace Middleware;

/**
 * マイグレーションクラス
 */
class Migration extends \Common\Core\Base
{
    /**
     * マイグレーション実行
     *
     * @param string $key
     * @param string $path
     * @return integer
     */
    public function migrate(string $key, string $path): int
    {
        $database = new Database($key);
        return $database->migration($path);
    }
}
