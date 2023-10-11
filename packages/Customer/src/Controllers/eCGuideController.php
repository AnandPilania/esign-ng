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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Core\Models\eCCompany;
use Core\Models\eCRole;
use Core\Models\eCCompanyConsignee;
use Core\Models\eCCompanyRemoteSign;
use Core\Models\eCUser;
use Customer\Services\eCGuideService;

class eCGuideController extends eCBaseController
{

    private $guideService;

    /**
     * eCConfigController constructor.
     * @param $guideService
     */
    public function __construct(eCGuideService $guideService)
    {
        $this->guideService = $guideService;
    }

    public function initGuide()
    {
        try {
            $result = $this->guideService->initGuideSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCGuideController][initGuide] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchGuide(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");
            $result = $this->guideService->searchGuide($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCGuideController][searchGuide] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDocumentDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->guideService->getDocumentDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCGuideConfigController][getDocumentDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getGuideDocument(Request $request) {
        try {
            $id = $request->id;
            $result = $this->guideService->getGuideDocument($id);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCGuideServiceController][getGuideDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initGuideVideo()
    {
        try {
            $result = $this->guideService->initGuideVideoSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCGuideServiceController][initGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchGuideVideo(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");
            $result = $this->guideService->searchGuideVideo($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCGuideServiceController][searchGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }


    public function getGuideVideoDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->guideService->getGuideVideoDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCGuideServiceController][getGuideVideoDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}    