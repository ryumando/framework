<?php

namespace Common\Core;

/**
 * View 基底クラス
 */
class View extends Base
{
  protected $path;

  /**
   * コンストラクタ
   *
   * @param string $path
   */
  public function __construct(string $path)
  {
    $this->path = App::$project_path . '/' . preg_replace('#^\.?/#', '', $path);
  }

  /**
   * View 読込
   *
   * @param mixed $res
   * @return boolean
   */
  public function call($res = null): bool
  {
    if (file_exists($this->path)) {
      require_once $this->path;
      return true;
    }
    return false;
  }
}