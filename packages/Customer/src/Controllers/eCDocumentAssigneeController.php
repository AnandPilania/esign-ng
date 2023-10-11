<?php


namespace Customer\Controllers;


use Carbon\Carbon;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\DocumentType;
use Core\Messages\ApiMessages;
use Core\Models\eCCompanyConfig;
use Core\Models\eCDocumentAssignee;
use Customer\Services\eCDocumentAssigneeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class eCDocumentAssigneeController extends eCBaseController
{
    private $userService;

    protected $guard = 'assignee';

    /**
     * eCDocumentAssigneeController constructor.
     * @param $userService
     */
    public function __construct(eCDocumentAssigneeService $userService)
    {
        $this->userService = $userService;
    }

    public function getProfile() {
        $assignee = auth('assign')->user();
        return $this->sendResponse($assignee, 'Get profile');
    }

    public function getConfigData(Request $request) {
        $code = $request->code;
        $assignee = eCDocumentAssignee::where('url_code', $code)->select('company_id')->first();
        $configData = eCCompanyConfig::find($assignee->company_id);
        return $this->sendResponse([
            'configData' => $configData
        ], 'Lấy thông tin cấu hình');
    }

    public function login(Request $request)
    {
//        return Hash::make("Le0qMX");
        try {
            $email = $request->json("email");
            $password = $request->json("password");
            $url_code = $request->json("url_code");
            if (strlen($password) < 6)   return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::PASSWORD_SHORT, array(), ApiHttpStatus::VALIDATION);
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:6',
                'url_code' => 'required'
            ];
            $validator = Validator::make([
                'email' => $email,
                'password' => $password,
                'url_code' => $url_code
            ], $rules);

            if ($validator->fails()) {
                return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::VALIDATE_FAILED, array(), ApiHttpStatus::VALIDATION);
            }
            $credentials = [
                'email' => Str::lower($request->json('email')),
                'password' => $request->json('password'),
                'url_code' => $request->json('url_code'),
            ];

            if (!$token = auth('assign')->attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp])) {
                return $this->sendError(ApiHttpStatus::PASSWORD_WRONG, ApiMessages::AUTH_FAILED, array(), ApiHttpStatus::PASSWORD_WRONG);
            }

            $user = auth('assign')->user();
            if ($user->status != 1) {
                    return $this->sendError(ApiHttpStatus::NOT_ACCEPTABLE, ApiMessages::ACCOUNT_NOT_ACCEPTABLE, array(), ApiHttpStatus::NOT_ACCEPTABLE);
            }

            //Step 2 : login
            $user['token'] = $token;

            $result = $this->userService->updateAccessToken($user);
            return $this->sendResponse($result, ApiMessages::AUTH_SUCCESS);

        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][login] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::INTERNAL_SERVER_ERROR, ApiMessages::ERROR_PROCESSING, $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function logout(array $guards = null) {
        $guards = $guards ?: array_keys(config('auth.guards'));

        foreach ($guards as $guard) {

            if ($guard instanceof \Illuminate\Auth\SessionGuard) {
                $guard->logout();
            }
        }
        return $this->sendResponse([], ApiMessages::LOG_OUT_SUCCESS);
    }

    public function isLogin(Request $request)
    {
        $user = auth('assign')->user();
        if (!$user) {
            return $this->sendError(['is_login' => false], "");
        }
        $code = $request->code;
        if ($user->url_code != $code) {
            return $this->sendError(['is_login' => false], "");
        }

        return $this->sendResponse([
            'is_login' => true
        ], ApiMessages::GET_INFO_LOGIN);
    }

    public function init() {
        try {
            $result = $this->userService->initViewDocument();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][init] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getSignDocument(Request $request) {
        try {
            $id = $request->id;
            $result = $this->userService->getSignDocument($id);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][getSignDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateSignature(Request $request) {
        try {
            $image_signature = $request->json("image_signature");
            $docId = $request->docId;
            $result = $this->userService->updateSignature($image_signature, $docId);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][updateSignature] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function saveSignatureLocation(Request $request) {
        try {
            $lstLocation = $request->location;
            $result = $this->userService->saveSignatureLocation($lstLocation);
            return $this->sendResponse($result, 'SERVER.UPDATE_SIGNATURE_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][saveSignatureLocation] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function approval(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->approveDocument($id, "");
            return $this->sendResponse($result, 'SERVER.APPROVAL_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][approval] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function transferDocument(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->transferDocument($request, "");
            return $this->sendResponse($result, 'SERVER.TRANSFER_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][transferDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function addApprovalAssignee(Request $request)
    {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->addApprovalAssignee($request);
            return $this->sendResponse($result, 'SERVER.ADD_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][addApprovalAssignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function hashDoc(Request $request) {
        try {
            $id = $request->docId;
            $pubKey = $request->pubKey;
            $result = $this->userService->hashDoc($id, $pubKey);
            return $this->sendResponse($result, "");
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][hashDoc] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signToken(Request $request){
        try {
            $id = $request->json("docId");
            $ip = $request->ip();
            $pubca = $request->pubca;
            $ca = $request->ca;
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->signDocument($id, $ip, $ca, $pubca);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][signToken] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signOtp(Request $request) {
        try {
            $id = $request->json("docId");
            $otp = $request->otp;
            $ip = $request->ip();
            if (!isset($id) || !isset($otp)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->signOtpDocument($id, $ip, $otp);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][signOtp] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function signKyc(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->signKycDocument($request);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][signKyc] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }


    public function signMySignDocument(Request $request) {
        try {
            $id = $request->json("docId");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->signMySignDocument($request);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signMySignDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function verifyOcr(Request $request) {
        try {
            $result = $this->userService->verifyOcr($request);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][verifyOcr] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deny(Request $request) {
        try {
            $id = $request->json("docId");
            $reason = $request->reason;
            if (!isset($id) || !isset($reason)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->denyDocument($id, $reason);
            return $this->sendResponse($result, 'SERVER.DENY_APPROVAL_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][deny] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function sendOtp(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->userService->sendOtp($id);
            return $this->sendResponse($result, 'SERVER.SEND_OTP_SUCCESS');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][sendOtp] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initGuide()
    {
        try {
            $result = $this->userService->initGuideSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][initGuide] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            $result = $this->userService->searchGuide($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][searchGuide] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDocumentDetail(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->getDocumentDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCDocumentAssigneeController][getDocumentDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getGuideDocument(Request $request)
    {
        try {
            $id = $request->id;
            $result = $this->userService->getGuideDocument($id);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][getGuideDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initGuideVideo()
    {
        try {
            $result = $this->userService->initGuideVideoSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][initGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            $result = $this->userService->searchGuideVideo($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCDocumentAssigneeController][searchGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }


    public function getGuideVideoDetail(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->userService->getGuideVideoDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCDocumentAssigneeController][getGuideVideoDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getListCts(Request $request) {
        try {
            $user_id = $request->json("user_id");
            $result = $this->userService->getListCts($user_id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][getDetailDocumentSampleById] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }public function registerCTS(Request $request) {
    try {
        $id = $request->json("docId");
        if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
        $result = $this->userService->registerCTS($request);
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
            $result = $this->userService->signICA($request);
            return $this->sendResponse($result, 'SERVER.SIGN_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCommerceController][signICA] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
