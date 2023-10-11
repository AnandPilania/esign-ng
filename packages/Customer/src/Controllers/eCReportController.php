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
use Customer\Services\eCReportService;

class eCReportController extends eCBaseController
{
    private $reportService;

    /**
     * eCReportController constructor.
     * @param $reportService
     */
    public function __construct(eCReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function initInternalDocument(Request $request)
    {
        try {
            $result = $this->reportService->initInternalDocumentSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][initInternalDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchInternalDocument(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->reportService->searchInternalDocument($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][searchInternalDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function exportInternalDocument(Request $request){
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $document_state = $request->document_state;
            $document_type_id = $request->document_type_id;
            $dc_style = $request->dc_style;
            $result = $this->reportService->exportInternalDocument($startDate, $endDate, $document_state, $document_type_id, $dc_style);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCReportController][exportInternalDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initCommerceDocument(Request $request)
    {
        try {
            $result = $this->reportService->initCommerceDocumentSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][initCommerceDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchCommerceDocument(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->reportService->searchCommerceDocument($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][searchCommerceDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function exportCommerceDocument(Request $request){
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $document_state = $request->document_state;
            $document_type_id = $request->document_type_id;
            $dc_style = $request->dc_style;
            $result = $this->reportService->exportCommerceDocument($startDate, $endDate, $document_state, $document_type_id, $dc_style);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCReportController][exportCommerceDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSendMessage(Request $request)
    {
        try {
            $result = $this->reportService->initSendMessageSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][initSendMessage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchSendMessage(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->reportService->searchSendMessage($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][searchSendMessage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function exportSendMessage(Request $request){
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $keyword = $request->keyword;
            $document_group_id = $request->document_group_id;
            $document_type_id = $request->document_type_id;
            $dc_style = $request->dc_style;
            $result = $this->reportService->exportSendMessage($startDate, $endDate, $keyword, $document_group_id, $document_type_id, $dc_style);

            return $result;
        } catch (Exception $e) {
            Log::error("[eCReportController][exportSendMessage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSignEkyc()
    {
        try {
            $result = $this->reportService->initSignEkycSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][initSignEkyc] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchSignEkyc(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->reportService->searchSignEkyc($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][searchSignEkyc] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function exportSignEkyc(Request $request){
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $keyword = $request->keyword;
            $result = $this->reportService->exportSignEkyc($startDate, $endDate, $keyword);

            return $result;
        } catch (Exception $e) {
            Log::error("[eCReportController][exportSignEkyc] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSignAssignee(Request $request)
    {
        try {
            $result = $this->reportService->initSignAssignee();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][initSignAssignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchSignAssignee(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->reportService->searchSignAssignee($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCReportController][searchSignAssignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function exportSignAssignee(Request $request){
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $keyword = $request->keyword;
            $document_group_id = $request->document_group_id;
            $source = $request->source;
            $type = $request->type;
            $result = $this->reportService->exportSignAssignee($startDate, $endDate, $document_group_id, $source, $keyword, $type);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCReportController][exportSignAssignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
