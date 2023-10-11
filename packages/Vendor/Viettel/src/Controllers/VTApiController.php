<?php

namespace Viettel\Controllers;

use Carbon\Carbon;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\DocumentType;
use Customer\Exceptions\eCBusinessException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Viettel\Services\VTApiService;

class VTApiController extends eCBaseController
{
    private $vtService;

    /**
     * eCInternalController constructor.
     * @param $documentService
     */
    public function __construct(VTApiService $vtService)
    {
        $this->vtService = $vtService;
    }

    public function getDocumentTemplate() {
        try {
            $result = $this->vtService->getDocumentTemplate();
            return $this->sendResponse($result, 'Success');
        } catch (Exception $e) {
            Log::error("[VTApiController][getDocumentTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDetailTemplate(Request $request) {
        try {
            $templateId = $request->template_id;
            $result = $this->vtService->getDetailTemplate($templateId);
            return $this->sendResponse($result, 'Success');
        } catch (Exception $e) {
            Log::error("[VTApiController][getDetailTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createDocumentFromTemplate(Request $request) {
        try {
            $result = $this->vtService->createDocumentFromTemplate($request);
            return $this->sendResponse($result, 'Tạo hợp đồng thành công');
        } catch (Exception $e) {
            Log::error("[VTApiController][createDocumentFromTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchDocumentList(Request $request) {
        try {
            $start = $request->json("start");
            $limit = $request->json("limit");
            $customerId = $request->json("customerId");
            $searchData = $request->json("searchData");

            $result = $this->vtService->searchDocumentList($searchData, $start, $limit, $customerId);
            return $this->sendResponse($result, 'Success');
        } catch (Exception $e) {
            Log::error("[VTApiController][searchDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getViewDocument(Request $request) {
        try {
            $id = $request->docId;
            $customerId = $request->customerId;
            $result = $this->vtService->getViewDocument($id, $customerId);
            return $this->sendResponse($result, 'Success');
        } catch (Exception $e) {
            Log::error("[VTApiController][getViewDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function downloadDocument(Request $request) {
        try {
            $id = $request->docId;
            $customerId = $request->customerId;
            $result = $this->vtService->downloadDocument($id, $customerId);
            return $result;
        } catch (Exception $e) {
            Log::error("[VTApiController][downloadDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function checkIdCard(Request $request) {
        $startTime=round(microtime(true) * 1000);
        try {
            $customerId = $request->customerId;
            $image = $request->image;
            $result = $this->vtService->checkIdCard($customerId, $image);
            $logDuration= round(microtime(true) * 1000)-$startTime;
            Log::info(date("Y-m-d H:i:s",time())."|".$request->ip()."|CHECK_IMAGE|".$logDuration."|SUCCESS");
            if ($result["code"] == 1) {
                return $this->sendResponse($result, 'Kiểm tra chất lượng ảnh thành công');
            }
            return $this->sendError($result["code"], $result["message"]);
        } catch (Exception $e) {
            Log::error("[VTApiController][checkIdCard] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            $logDuration= round(microtime(true) * 1000)-$startTime;
            Log::info(date("Y-m-d H:i:s",time())."|".$request->ip()."|CHECK_IMAGE|".$logDuration."|FAIL");
            return $this->handleException($e);
        }
    }

    public function ocrIdCard(Request $request) {
        $startTime=round(microtime(true) * 1000);
        try {
            $customerId = $request->customerId;
            $image_front = $request->image_front;
            $image_back = $request->image_back;
            $result = $this->vtService->ocrIdCard($customerId, $image_front, $image_back);
            $logDuration= round(microtime(true) * 1000)-$startTime;
            Log::info(date("Y-m-d H:i:s",time())."|".$request->ip()."|OCR_ID_CARD|".$logDuration."|SUCCESS");
            if ($result["code"] == 1) {
                return $this->sendResponse($result, 'Bóc tách dữ liệu thành công');
            }
            return $this->sendError($result["code"], $result["message"]);
        } catch (Exception $e) {
            Log::error("[VTApiController][ocrIdCard] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            $logDuration= round(microtime(true) * 1000)-$startTime;
            Log::info(date("Y-m-d H:i:s",time())."|".$request->ip()."|OCR_ID_CARD|".$logDuration."|FAIL");
            return $this->handleException($e);
        }
    }

    public function signEkyc(Request $request) {
        try {
            $docId = $request->docId;
            $customerId = $request->customerId;
            $face_image = $request->face_image;
            $image_signature = $request->image_signature;
            $ip = $request->ip();
            $result = $this->vtService->signEkyc($docId, $customerId, $face_image, $ip, $image_signature);
            return $this->sendResponse($result, 'Ký hợp đồng thành công');
        } catch (Exception $e) {
            Log::error("[VTApiController][signEkyc] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
