<?php

namespace Middleware;

/**
 * ルーティングクラス
 */
class Routing implements \Common\Core\Interfaces\Routing
{

  /**
   * ルーティング設定
   *
   * @return array
   */
  public static function routes(): array
  {
    return [
      ['.*', 'Controllers\SampleController', 'index'],
    ];
  }

}