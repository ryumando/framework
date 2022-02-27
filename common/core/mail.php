<?php

namespace Common\Core;

/**
 * メールクラス
 */
class Mail extends Base
{
    /** @var string メール件名 */
    private $subject;

    /** @var string メール本文 */
    private $message;

    /** @var array 添付ファイル */
    private $files;

    /** @var array 宛先 */
    private $to;

    /** @var array 送信者 */
    private $from;

    /** @var array CC */
    private $cc;

    /** @var array BCC */
    private $bcc;

    /** @var string エラー通知先 */
    private $return_path;

    /** @var array 返信先 */
    private $reply_to;

    /** @var string 組織名 */
    private $organization;

    /** @var int 重要度 */
    private $priority;

    /** @var string 文字コード */
    private $encoding;

    /** @var bool HTMLメールフラグ */
    private $html;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * プロパティ初期化
     *
     * @return object
     */
    public function reset(): object
    {
        $this->subject = null;
        $this->message = null;
        $this->files = [];
        $this->to = [];
        $this->from = [];
        $this->cc = [];
        $this->bcc = [];
        $this->return_path = null;
        $this->reply_to = [];
        $this->organization = null;
        $this->priority = null;
        $this->encoding = 'UTF-8';
        $this->html = false;
        return $this;
    }

    /**
     * メール送信
     *
     * @return boolean
     */
    public function send(): bool
    {
        // バリデーション
        if (empty($this->to) || empty($this->from)) return false;

        // Content-Type 設定
        if ($this->html === true) {
            $content_type = 'Content-Type: text/html; charset=' . $this->encoding;
        } else {
            $content_type = 'Content-Type: text/plain; charset=' . $this->encoding;
        }

        // Content-Transfer-Encoding 設定
        if ($this->encoding === 'UTF-8'){
            $content_transfer_encoding = 'Content-Transfer-Encoding: 8bit';
        } else {
            $content_transfer_encoding = 'Content-Transfer-Encoding: 7bit';
        }

        // バウンダリー設定
        if (!empty($this->files)) $boundary = '__BOUNDARY_' . mt_rand() . '__';

        // 件名設定
        $subject = mb_encode_mimeheader($this->subject, $this->encoding);

        // 宛先設定
        $to = $this->convertListToHeader($this->to, $this->encoding);

        // 送信者設定
        $from = $this->convertListToHeader([$this->from], $this->encoding);

        // 返信先設定
        if (!empty($this->reply_to)) {
            $reply_to = $this->convertListToHeader([$this->reply_to], $this->encoding);
        } else {
            $reply_to = $from;
        }

        // ヘッダー生成
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        if (!empty($this->files)) {
            $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
        } else {
            $headers[] = $content_type;
        }
        $headers[] = $content_transfer_encoding;
        $headers[] = 'From: ' . $from;
        $headers[] = 'Sender: ' . $from;
        $headers[] = 'Reply-To: ' . $reply_to;
        if (!empty($this->cc)) {
            $headers[] = 'Cc: ' . $this->convertListToHeader($this->cc, $this->encoding);
        }
        if (!empty($this->bcc)) {
            $headers[] = 'Bcc: ' . $this->convertListToHeader($this->bcc, $this->encoding);
        }
        if (!empty($this->organization)) {
            $headers[] = 'Organization: ' . mb_encode_mimeheader($this->organization, $this->encoding);
        }
        if (!empty($this->priority)) {
            $headers[] = 'X-Priority: ' . $this->priority;
        }

        // パラメータ設定
        if (!empty($this->return_path)) {
            $params = '-f' . $this->return_path;
        } else {
            $params = '-f' . $this->from['mail'];
        }

        // 本文設定
        $message = [];
        if (empty($this->files)) {
            $message[] = mb_convert_encoding($this->message, $this->encoding);
        } else {
            $message[] = '--' . $boundary;
            $message[] = $content_type;
            $message[] = $content_transfer_encoding;
            $message[] = '';
            $message[] = mb_convert_encoding($this->message, $this->encoding);
            // 添付ファイル
            foreach ($this->files as $file) {
                $message[] = '--' . $boundary;
                $message[] = 'Content-Type: application/octet-stream; name="' . mb_encode_mimeheader($file['name'], $this->encoding) . '"';
                $message[] = 'Content-Disposition: attachment; filename="' . mb_encode_mimeheader($file['name'], $this->encoding) . '"';
                $message[] = 'Content-Transfer-Encoding: base64';
                $message[] = '';
                $message[] = chunk_split(base64_encode($file['binary']));
            }
        }

        // 送信
        return mail ($to, $subject, implode ("\r\n", $message), implode ("\r\n", $headers), $params);
    }

    /**
     * リスト変換
     *
     * @param array $list
     * @param string $encoding
     * @return string
     */
    private static function convertListToHeader(array $list, string $encoding): string
    {
        $header = '';
        foreach ($list as $val) {
            if (!empty($header)) $header .= ',';
            if (!empty($val['name'])) {
                $header .= mb_encode_mimeheader($val['name'], $encoding) . '<' . $val['mail'] . '>';
            } else {
                $header .= $val['mail'];
            }
        }
        return $header;
    }

    /**
     * 件名設定
     *
     * @param string $subject
     * @return object
     */
    public function subject(string $subject): object
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * 本文設定
     *
     * @param string $message
     * @return object
     */
    public function message(string $message): object
    {
        $this->message = $message;
        return $this;
    }

    /**
     * 添付ファイル追加
     *
     * @param string $name
     * @param $binary
     * @return object
     */
    public function file(string $name, $binary): object
    {
        $this->files[] = [
            'name' => $name,
            'binary' => $binary
        ];
        return $this;
    }

    /**
     * 宛先追加
     *
     * @param string $mail
     * @param string|null $name
     * @return object
     */
    public function to(string $mail, string $name = null): object
    {
        $this->to[] = [
            'mail' => $mail,
            'name' => $name
        ];
        return $this;
    }

    /**
     * 送信者設定
     *
     * @param string $mail
     * @param string|null $name
     * @return object
     */
    public function from(string $mail, string $name = null): object
    {
        $this->from = [
            'mail' => $mail,
            'name' => $name
        ];
        return $this;
    }

    /**
     * CC追加
     *
     * @param string $mail
     * @param string|null $name
     * @return object
     */
    public function cc(string $mail, string $name = null): object
    {
        $this->cc[] = [
            'mail' => $mail,
            'name' => $name
        ];
        return $this;
    }

    /**
     * BCC追加
     *
     * @param string $mail
     * @param string|null $name
     * @return object
     */
    public function bcc(string $mail, string $name = null): object
    {
        $this->bcc[] = [
            'mail' => $mail,
            'name' => $name
        ];
        return $this;
    }

    /**
     * エラー通知先設定
     *
     * @param string $return_path
     * @return object
     */
    public function returnPath(string $return_path): object
    {
        $this->return_path = $return_path;
        return $this;
    }

    /**
     * 返信先設定
     *
     * @param string $mail
     * @param string|null $name
     * @return object
     */
    public function replyTo(string $mail, string $name = null): object
    {
        $this->reply_to = [
            'mail' => $mail,
            'name' => $name
        ];
        return $this;
    }

    /**
     * 組織設定
     *
     * @param string $organization
     * @return object
     */
    public function organization(string $organization): object
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * 重要度設定
     *
     * @param integer $priority
     * @return object
     */
    public function priority(int $priority): object
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * 文字コード設定
     *
     * @param string $encoding
     * @return object
     */
    public function encoding(string $encoding): object
    {
        $this->encoding = strtoupper($encoding);
        return $this;
    }

    /**
     * HTMLメール設定
     *
     * @param boolean $html
     * @return object
     */
    public function isHtml(bool $html): object
    {
        $this->html = $html;
        return $this;
    }

}