<?php

namespace Customer\Controllers;

use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Customer\Services\eCLogService;

class eCLogController extends eCBaseController
{
    private $logService;

    /**
     * eCLogController constructor.
     * @param $logService
     */
    public function __construct(eCLogService $logService)
    {
        $this->logService = $logService;
    }

    public function initLog(Request $request)
    {
        try {
            $result = $this->logService->initLogSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCLogController][initLog] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchLog(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->logService->searchLog($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCLogController][searchLog] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
