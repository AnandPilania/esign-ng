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
use Customer\Services\eCDocumentSampleService;

class eCDocumentSampleController extends eCBaseController
{
    private $documentSampleService;

    /**
     * eCDocumentSampleController constructor.
     * @param $documentSampleService
     */
    public function __construct(eCDocumentSampleService $documentSampleService)
    {
        $this->documentSampleService = $documentSampleService;
    }

    public function initDocumentSample()
    {
        try {
            $result = $this->documentSampleService->initDocumentSampleSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][initDocumentSample] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchDocumentSample(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->documentSampleService->searchDocumentSample($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][searchDocumentSample] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewDocumentSample(Request $request)
    {
        try {
            $name = $request->json("name");
            $description = $request->json("description");
            $document_type = $request->json("document_type");
            $document_type_id = $request->json("document_type_id");
            $expired_type = $request->json("expired_type");
            $expired_month = $request->json("expired_month");
            $is_encrypt = $request->json("is_encrypt");
            $save_password = $request->json("encrypt_password");
            $files = $request->json('files');
            $ip = $request->ip();
            Log::info($is_encrypt);

            $postData = [
                'name' => $name,
                'description' => $description,
                'document_type' => $document_type,
                'expired_month' => $expired_month,
                'expired_type' => $expired_type,
                'document_type_id' => $document_type_id,
                'is_encrypt' => $is_encrypt,
                'save_password' => $save_password,
            ];

            $result = $this->documentSampleService->insertDocumentSample($postData, $files, $ip);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][createNewDocumentSample] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateDocumentSample(Request $request)
    {
        try {
            $id = $request->json("id");
            $name = $request->json("name");
            $description = $request->json("description");
            $document_type = $request->json("document_type");
            $document_type_id = $request->json("document_type_id");
            $expired_type = $request->json("expired_type");
            $expired_month = $request->json("expired_month");
            $files = $request->json('files');
            $ip = $request->ip();


            $postData = [
                'name' => $name,
                'description' => $description,
                'document_type' => $document_type,
                'expired_month' => $expired_month,
                'expired_type' => $expired_type,
                'document_type_id' => $document_type_id,
            ];

            $result = $this->documentSampleService->updateDocumentSample($id, $postData, $files, $ip);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][updateDocumentSample] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deleteDocumentSample(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentSampleService->deleteDocumentSampleSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCDocumentSampleController][deleteDocumentSample] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiDocumentSample(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->documentSampleService->deleteDocumentSampleSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][deleteMultiDocumentSample] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function uploadDocumentFiles(Request $request) {
        try {
            $result = $this->documentSampleService->uploadFiles($request);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][uploadDocumentFiles] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function removeDocumentFile(Request $request) {
        try {
            $file_id = $request->json("file_id");
            $result = $this->documentSampleService->removeFile($file_id);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][removeDocumentFile] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDocumentDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->documentSampleService->getDocumentDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCDocumentSampleController][getDocumentDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function saveDetailSampleDocument(Request $request) {
        try {
            $result = $this->documentSampleService->saveDetailSampleDocument($request);
            return $this->sendResponse($result, 'SERVER.SAVE_DETAIL_SAMPLE_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][saveDetailSampleDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getSampleDocument(Request $request) {
        try {
            $id = $request->id;
            $ip = $request->ip();
            $result = $this->documentSampleService->getSampleDocument($id, $ip);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][getSampleDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function getFileSampleDocument(Request $request) {
        try {
            $id = $request->id;
            $result = $this->documentSampleService->getFileSampleDocument($id);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCDocumentSampleController][getSampleDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
