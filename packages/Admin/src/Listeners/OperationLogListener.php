<?php


namespace Admin\Listeners;


use Admin\Events\OperationLogEvent;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


class OperationLogListener implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(OperationLogEvent $event)
    {
        $scope = $event->scope;
        $sql = $event->query;
        $user_id = $event->user_id;
        $email = $event->email;
        $uri = $event->uri;

        if ($this->dbCondition($sql)) {
            $parse = $this->parseFunctionality($uri);
            $type = $this->startsWith($sql, 'insert') ? 'INSERT' : ($this->startsWith($sql, 'update') ? 'UPDATE' : 'DELETE');
            $log = '[' . date('Y-m-d H:i:s') . ']' . " " . '[ADMIN]' . " " . $user_id . " " . base64_encode($email)
                . " " . $parse['code']
                . " " . $parse['name']
                . " " . $type
                . " " . base64_encode($sql);

            Log::info($log);
        }
    }

    private function dbCondition($sql)
    {
        if ($this->startsWith($sql, 'insert') || $this->startsWith($sql, 'update') || $this->startsWith($sql, 'delete'))
            return true;
        return false;
    }

    function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (strtoupper(substr($string, 0, $len)) === strtoupper($startString));
    }

    private function parseFunctionality($uri)
    {
        try {
            if (env('SESSION_DRIVER') == 'redis') {
                $key = 'admin::f';
                $results = Redis::get($key);
                if (!isset($results))
                    return [
                        'code' => $uri,
                        'name' => $uri
                    ];
                $rs = json_decode($results);
                $ad = explode("/", $uri);
                $code = $ad[2];
                foreach ($rs as $r) {
                    $len = similar_text($code, $r->code);
                    if ($len > strlen($code) - 1) {
                        return [
                            'code' => $r->code,
                            'name' => $r->name
                        ];
                    }
                }
            }
            return [
                'code' => $uri,
                'name' => $uri
            ];
        } catch (Exception $e) {
            return [
                'code' => $uri,
                'name' => $uri
            ];
        }
    }
}
