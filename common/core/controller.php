<?php

namespace Common\Core;

/**
 * Controller 基底クラス
 */
class Controller extends Base
{
  /**
   * View 読込
   *
   * @param string $path
   * @param mixed $res
   * @param boolean $escape
   * @param boolean $exit
   * @return void
   */
  protected function view(string $path, $res = null, bool $escape = false, bool $exit = true): void
  {
    $view = new View($path);
    if ($escape) $res = Util::escapeHtmlTags($res);
    if (!$view->call($res)) http_response_code(404);
    if ($exit) exit;
  }

}