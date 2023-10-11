<?php


namespace Report\Services;


use Illuminate\Support\Facades\Log;

class AbstractService
{
    protected $logger;

    /**
     * AbstractService constructor.
     */
    public function __construct($clazz = '')
    {
        $this->logger = $clazz;
    }

    protected function log($level = 'INFO', $log = '')
    {
        switch ($level) {
            case 'WARN':
                Log::warning("[" . $this->logger . "] ===>>>" . $log);
                Log::channel('slack')->warning('Sokoyaka [' . date('y-m-d') . '] ===>>>' . $log);
                break;
            case 'ERROR':
                Log::error("[" . $this->logger . "] ===>>>" . $log);
                Log::channel('slack')->error('Sokoyaka [' . date('y-m-d') . '] ===>>>' . $log);
                break;
            default:
                Log::info("[" . $this->logger . "] ===>>>" . $log);
        }
    }
}
