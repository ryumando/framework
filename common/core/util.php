<?php

namespace Common\Core;

/**
 * ユーティリティークラス
 */
class Util
{

  /**
   * HTMLタグエスケープ
   *
   * @param mixed $data
   * @param string $encode
   * @return mixed
   */
  public static function escapeHtmlTags($data, string $encode = 'UTF-8')
  {
    if (is_string($data)) {
      $data = htmlentities ($data, ENT_QUOTES, $encode, false);
    } else if (is_array($data)) {
      foreach ($data as $key => $val) {
        $data[$key] = self::escapeHtmlTags($val, $encode);
      }
    } else if (is_object($data)) {
      foreach ($data as $key => $val) {
        $data->$key = self::escapeHtmlTags($val, $encode);
      }
    }
    return $data;
  }

}