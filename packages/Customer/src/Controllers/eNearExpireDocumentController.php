<?php


namespace Customer\Controllers;

use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\DocumentHandler;
use Customer\Services\eDocumentHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class eNearExpireDocumentController extends eCBaseController
{
    private $documentHandlerService;

    //
    /**
     * eExpiredDocumentController constructor.
     * @param $expiredDocumentService
     */

    public function __construct(eDocumentHandlerService $documentHandlerService)
    {
        $this->documentHandlerService = $documentHandlerService;
    }

    public function initDocumentList()
    {
        try {
            $result = $this->documentHandlerService->initDocumentList(DocumentHandler::NearExpire); //1: tai lieu sap het han
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eExpiredDocumentController][initDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function changeDocumentGroup(Request $request)
    {
        try {
            $groupId = $request->json("groupId");
            $result = $this->documentHandlerService->changeDocumentGroup($groupId);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eExpiredDocumentController][changeDocumentGroup] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchDocumentList(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->documentHandlerService->searchDocumentList($searchData, $draw, $start, $limit, $sortQuery, DocumentHandler::NearExpire);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eExpiredDocumentController][searchDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //hop dong sap qua han
    public function renewMultiDocumentList(Request $request)
    {
        try {

            $lstDocumentList = $request->json("lst_document_list");
            $emailContent = $request->json("email_content");
            $expiredDate = $request->json("expired_date");

            $postData = [
                'lst_document_list' => $lstDocumentList,
                'email_content' => $emailContent,
                'expired_date' => $expiredDate
            ];

            //check validate
            if (!isset($postData['lst_document_list']) || count($postData['lst_document_list']) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            if (!isset($postData['expired_date']) || empty($postData['expired_date'])) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }

            $result = $this->documentHandlerService->renewDocumentListSetting($postData);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eExpiredDocumentController][renewMultiDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
