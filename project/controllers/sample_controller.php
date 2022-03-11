<?php

namespace Controllers;

/**
 * サンプル コントローラークラス
 */
class SampleController extends \Common\Core\Controller
{
  public function index(...$args)
  {
    var_dump($_ENV);exit;
    
    $res = [
      'title' => 'Framework Sample',
      'body' => '< Hello! World >',
    ];
    $this->view('views/sample_view.php', $res, true);
  }
}