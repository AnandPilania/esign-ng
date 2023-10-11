<?php


namespace Admin\Services\Shared;


use Core\Helpers\Common;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendNotificationApiAccount($email, $phoneNumber, $template, $exts, $type) {
        $url = Common::getConverterServer() . '/api/v1/notify/account';
        $data = array(
            "email" => $email,
            "number_phone" => $phoneNumber,
            "template_name" => $template,
            "exts" => $exts,
            "type" => $type
        );

        $header = array(
            'Content-Type: application/json'
        );
        Log::info($data);
        $sendRes = Common::httpPost($url, $data, $header);
        Log::info($sendRes);
    }

}
