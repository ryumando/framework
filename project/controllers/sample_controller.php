<?php

namespace Controllers;

/**
 * サンプル コントローラークラス
 */
class SampleController extends \Common\Core\Controller
{
  public function index(...$args)
  {
    $res = [
      'title' => 'Framework Sample',
      'body' => '< Hello! World >',
    ];
    $this->view('views/sample_view.php', $res, true);
  }
}