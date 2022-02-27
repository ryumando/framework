<?php

namespace Common\Core;

/**
 * 基底クラス
 */
class Base
{
    /**
     * 設定値取得
     *
     * @param string $key
     * @return mixed
     */
    protected static function config(string $key)
    {
        $config = App::$config;
        $keys = explode('.', $key);
        $depth = count($keys);
        if ($depth === 3 && isset($config[$keys[0]][$keys[1]][$keys[2]])) return $config[$keys[0]][$keys[1]][$keys[2]];
        if ($depth === 2 && isset($config[$keys[0]][$keys[1]])) return $config[$keys[0]][$keys[1]];
        if ($depth === 1 && isset($config[$keys[0]])) return $config[$keys[0]];
        return null;
    }
}