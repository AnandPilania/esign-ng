<?php

namespace Customer\Controllers;

use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\DocumentType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Customer\Services\eCDocumentService;

class eCCommerceController extends eCBaseController
{
    private $documentService;

    /**
     * eCCommerceController constructor.
     * @param $documentService
     */
    public function __construct(eCDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function initCreateCommerceDocument(Request $request)
    {
        try {
            $id = $request->json("docId");
            $result = $this->documentService->initCreateDocument($id, DocumentType::COMMERCE, DocumentType::COMMERCE);

            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initPosition] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDocumentCodeByDocumentTypeId(Request $request) {
        try {
            $id = $request->json("id");

            $result = $this->documentService->getDocumentCodeByDocumentTypeId($id, DocumentType::COMMERCE); //1: tai lieu noi bo
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][getDocumentCodeByDocumentTypeId] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createDocumentFromTemplate(Request $request) {
        try {
            $result = $this->documentService->createDocumentFromTemplate($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.FINISH_DRAFTING_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][createDocumentFromTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDetailDocumentSampleById(Request $request) {
        try {
            $id = $request->json("id");

            $result = $this->documentService->getDetailDocumentSampleById($id, DocumentType::COMMERCE); //1: tai lieu noi bo
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][getDetailDocumentSampleById] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function uploadDocumentFiles(Request $request) {
        try {
            $result = $this->documentService->uploadFiles($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][uploadDocumentFiles] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function removeDocumentFile(Request $request) {
        try {
            $file_id = $request->json("file_id");
            $result = $this->documentService->removeFile($file_id);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][removeDocumentFile] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getSignDocument(Request $request) {
        try {
            $id = $request->id;
            $ip = $request->ip();

            $result = $this->documentService->getSignDocument($id,$ip);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCCommerceController][getSignDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getFatherInfo(Request $request)
    {
        try {
            $id = $request->json("parent_id");
            $result = $this->documentService->getFatherInfo($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCInternalController][getFatherInfo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function goStep2(Request $request) {
        try {
            $result = $this->documentService->goStep2($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][goStep2] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function goStep3(Request $request) {
        try {
            $result = $this->documentService->goStep3($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][goStep3] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function finishDrafting(Request $request) {
        try {
            $result = $this->documentService->finishDrafting($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.FINISH_DRAFTING_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][finishDrafting] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initViewCommerceDocument(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->documentService->initViewDocument($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initViewCommerceDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function selectSignature(Request $request) {
        try {
            $assigneeId = $request->assignee;
            $type = $request->type;
            $result = $this->documentService->selectSignature($assigneeId, $type, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SELECT_SIGNATURE_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][selectSignature] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateSignatureLocation(Request $request) {
        try {
            $assigneeId = $request->assigneeId;
            $lstLocation = $request->location;
            $result = $this->documentService->updateSignatureLocation($assigneeId, $lstLocation, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.UPDATE_SIGNATURE_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][updateSignatureLocation] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function hashDoc(Request $request) {
        try {
            $id = $request->docId;
            $pubKey = $request->pubKey;
            $result = $this->documentService->hashDoc($id, $pubKey);
            return $this->sendResponse($result, "");
        } catch (Exception $e) {
            Log::error("[eCCommerceController][sendOtp] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function sendOtp(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->documentService->sendOtp($id, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SEND_OTP_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][sendOtp] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getHistoryDocument(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->documentService->getHistoryDocument($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][getHistoryDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initDocumentList()
    {
        try {
            $result = $this->documentService->initDocumentListSetting(DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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

            $result = $this->documentService->searchDocumentList($searchData, $draw, $start, $limit, $sortQuery, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][searchDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiDocumentList(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->documentService->deleteDocumentListSetting($ids, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][deleteMultiDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSignManage()
    {
        try {
            $result = $this->documentService->initSignManageSetting(DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initSignManage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchSignManage(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->documentService->searchSignManage($searchData, $draw, $start, $limit, $sortQuery, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][searchSignManage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initApprovalManage()
    {
        try {
            $result = $this->documentService->initApprovalManageSetting(DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initApprovalManage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchApprovalManage(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->documentService->searchApprovalManage($searchData, $draw, $start, $limit, $sortQuery, DocumentType::COMMERCE); //1: tai lieu noi bo
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][searchApprovalManage] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function approveDocument(Request $request){
        try {
            $id = $request->json("docId");
            $ip = $request->ip();
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->approveDocument($id, "", DocumentType::COMMERCE,$ip);
            return $this->sendResponse($result, 'SERVER.APPROVAL_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][approveDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signDocument(Request $request){
        try {
            $id = $request->json("docId");
            $ip = $request->ip();
            $pubca = $request->pubca;
            $ca = $request->ca;
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->signDocument($id, $ip, DocumentType::COMMERCE, $pubca, $ca);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signOtpDocument(Request $request) {
        try {
            $id = $request->json("docId");
            $ip = $request->ip();
            $otp = $request->otp;
            if (!isset($id) || !isset($otp)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->signOtpDocument($id, $ip, $otp, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signOtpDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signKycDocument(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->signKycDocument($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signKycDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signMySignDocument(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->signMySignDocument($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signMySignDocument] cause:  "  . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function verifyOcr(Request $request) {
        try {
            $result = $this->documentService->verifyOcr($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][verifyOcr] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function denyDocument(Request $request) {
        try {
            $id = $request->json("docId");
            $reason = $request->reason;
            if (!isset($id) || !isset($reason)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->denyDocument($id, $reason, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.DENY_APPROVAL_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][denyDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function editDenyDocument(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->editDenyDocument($id, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][editDenyDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function approveDenyApprovalDocument(Request $request) {
        try {
            $id = $request->json("docId");
            $ip = $request->ip();
            $params = $request->params;
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            if ($params["isAgree"]) {
                $result = $this->documentService->approveDocument($id, $params["content"], DocumentType::COMMERCE,$ip);
                return $this->sendResponse($result, 'SERVER.APPROVAL_SUCCESSFUL');
            } else {
                if ($params["content"] == "") {
                    return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
                }
                $result = $this->documentService->denyDocument($id, $params["content"], DocumentType::COMMERCE);
                return $this->sendResponse($result, 'SERVER.DENY_APPROVAL_SUCCESSFUL');
            }

        } catch (Exception $e) {
            Log::error("[eCCommerceController][approveDenyApprovalDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function approveDenyMultiApprovalDocument(Request $request){
        try {
            $lstId = $request->json("lstDocId");
            $ip = $request->ip();
            $params = $request->params;
            if (!isset($lstId) || count($lstId) == 0) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            if ($params["isAgree"]) {
                foreach ($lstId as $id) {
                    $this->documentService->approveDocument($id, $params["content"], DocumentType::COMMERCE,$ip);
                }
                return $this->sendResponse([], 'SERVER.APPROVAL_SUCCESSFUL');
            } else {
                if ($params["content"] == "") {
                    return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
                }
                foreach ($lstId as $id) {
                    $this->documentService->denyDocument($id, $params["content"], DocumentType::COMMERCE);
                }
                return $this->sendResponse([], 'SERVER.DENY_APPROVAL_SUCCESSFUL');
            }

        } catch (Exception $e) {
            Log::error("[eCCommerceController][approveDenyMultiApprovalDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signMultiDocument(Request $request) {
        try {
            $lstId = $request->json("lstDocId");
            $ip = $request->ip();
            $pubca = $request->pubca;
            $ca = $request->ca;
            $params = $request->params;
            if (!isset($lstId) || count($lstId) == 0) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            foreach ($lstId as $id) {
                $this->documentService->signDocument($id, $ip, DocumentType::COMMERCE, $pubca, $ca);
            }
            return $this->sendResponse([], 'SERVER.SIGN_SUCCESSFUL');

        } catch (Exception $e) {
            Log::error("[eCCommerceController][signMultiDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSendEmail()
    {
        try {
            $result = $this->documentService->initSendEmailSetting(DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initSendEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchSendEmail(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->documentService->searchSendEmail($searchData, $draw, $start, $limit, $sortQuery, DocumentType::COMMERCE);
            // return response()->json($result);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][searchSendEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function sendSingleSendEmail(Request $request){
        try {
            $id = $request->json("convId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->sendEmailSetting([$id], DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SEND_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCCommerceController][sendSingleEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function sendMultiSendEmail(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->documentService->sendEmailSetting($ids, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SEND_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][sendMultiSendEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteSendEmail(Request $request){
        try {
            $id = $request->json("convId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->deleteSendEmailSetting([$id], DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCCommerceController][deleteSendEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiSendEmail(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->documentService->deleteSendEmailSetting($ids, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][deleteMultiSendEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSendSms()
    {
        try {
            $result = $this->documentService->initSendSmsSetting(DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][initSendSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchSendSms(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->documentService->searchSendSms($searchData, $draw, $start, $limit, $sortQuery, DocumentType::COMMERCE);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][searchSendSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function sendSingleSendSms(Request $request){
        try {
            $id = $request->json("convId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->sendSmsSetting([$id], DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SEND_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCCommerceController][sendSingleSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function sendMultiSendSms(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->documentService->sendSmsSetting($ids, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SEND_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][sendMultiSendSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteSendSms(Request $request){
        try {
            $id = $request->json("convId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->deleteSendSmsSetting([$id], DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCCommerceController][deleteSendSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiSendSms(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->documentService->deleteSendSmsSetting($ids, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][deleteMultiSendSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }


    public function getListCts(Request $request) {
        try {
            $user_id = $request->json("user_id");
            $result = $this->documentService->getListCts($user_id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][getListCts] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function registerCTS(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->registerCTS($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.REGISTER_CTS_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][registerCTS] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function signICA(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentService->signICA($request, DocumentType::COMMERCE);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signICA] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
