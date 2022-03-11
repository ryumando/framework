<?php

namespace Middleware;

/**
 * メールクラス
 */
class Mail extends \Common\Core\Mail
{

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        # $this->to(self::env('MAIL_TO'))
        #     ->from(self::env('MAIL_FROM'))
        #     ->subject(self::env('MAIL_SUBJECT'));
    }
}