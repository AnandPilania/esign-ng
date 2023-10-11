<?php

namespace Admin\Controllers;

use Admin\Services\ReportService;
use Core\Controllers\eCBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportController extends eCBaseController
{

    private $reportService;

    protected $guard = 'admin';

    /**
     * LoginController constructor.
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function guard()
    {
        return Auth::guard($this->guard);
    }

    public function init() {
        try {
            $result = $this->reportService->init();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[ReportController][init] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function search(Request $request) {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            switch ($searchData['type']) {
                case 0:
                    $result = $this->reportService->getTurnOver($searchData, $draw, $start, $limit, $sortQuery);
                    break;
                case 1:
                    $result = $this->reportService->getCustomers($searchData, $draw, $start, $limit, $sortQuery);
                    break;
                case 2:
                    $result = $this->reportService->getDocuments($searchData, $draw, $start, $limit, $sortQuery);
                    break;
            }

            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[ReportController][search] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function export(Request $request) {
        try {
            switch ($request->type) {
                case 0:
                    $result = $this->reportService->exportTurnOver($request);
                    break;
                case 1:
                    $result = $this->reportService->exportCustomer($request);
                    break;
                case 2:
                    $result = $this->reportService->exportDocuments($request);
                    break;
            }

            return $result;
        } catch (Exception $e) {
            Log::error("[eCReportController][exportSignAssignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
