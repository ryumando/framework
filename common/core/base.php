<?php

namespace Common\Core;

/**
 * 基底クラス
 */
class Base
{
    /**
     * 環境設定取得
     *
     * @param string $name
     * @return string
     */
    protected static function env(string $name)
    {
        return getenv($name);
    }
}