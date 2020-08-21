<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2018/8/22
 * Time: 20:39
 */

namespace App\Services\BaseService;

use App\Services\Service;
use Illuminate\Contracts\Mail\Mailer;

class EmailService extends Service
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * 发送邮件
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $toEmail
     * @param $data
     * @param $subject
     *
     * @return bool
     */
    public function sendEmail($toEmail, $data, $subject)
    {
        $this->mailer->send('__layout.email', $data, function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)->subject($subject);
        });

        if (count($this->mailer->failures()) > 0) {
            return false;
        }

        return true;
    }
}
