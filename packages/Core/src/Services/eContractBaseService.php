<?php

namespace Core\Services;

use Illuminate\Support\Facades\Log;

class eContractBaseService
{
    private $name;

    const INFO = 'INFO';
    const WARM = 'WARM';
    const ERROR = 'ERROR';

    /**
     * PTApiAbstractService constructor.
     * @param $name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

    protected function writeLog($log, $level = 'INFO')
    {
        switch ($level) {
            case self::WARM:
                Log::warning("eContract#2022 [$this->name] API [" . date('y-m-d') . "] ===>>>" . $log);
                break;
            case self::ERROR:
                Log::error("eContract#2022 [$this->name] API [" . date('y-m-d') . "] ===>>>" . $log);
                break;
            default:
                Log::info("eContract#2022 [$this->name] API [" . date('y-m-d') . "] ===>>>" . $log);
        }
    }
}
