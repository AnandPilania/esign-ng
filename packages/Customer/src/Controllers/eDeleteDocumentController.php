<?php


namespace Customer\Controllers;


use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\DocumentHandler;
use Customer\Services\eDocumentHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class eDeleteDocumentController extends eCBaseController
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
            $result = $this->documentHandlerService->initDocumentList(DocumentHandler::DELETE); //4: tai lieu bi huy bo;
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

            $result = $this->documentHandlerService->searchDocumentList($searchData, $draw, $start, $limit, $sortQuery, DocumentHandler::DELETE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eExpiredDocumentController][searchDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function restoreMultiDocumentList(Request $request)
    {
        try {

            $lstDocumentList = $request->json("lst_document_list");
            $emailContent = $request->json("email_content");

            $postData = [
                'lst_document_list' => $lstDocumentList,
                'email_content' => $emailContent,
            ];

            //check validate
            if (!isset($postData['lst_document_list']) || count($postData['lst_document_list']) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }

            $result = $this->documentHandlerService->restoreMultiDocumentList($postData);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eExpiredDocumentController][restoreMultiDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
