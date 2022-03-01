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
        # $this->to(self::config('mail.to'))
        #     ->from(self::config('mail.from'))
        #     ->subject(self::config('mail.subject'));
    }
}