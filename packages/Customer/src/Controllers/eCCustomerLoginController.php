<?php


namespace Customer\Controllers;


use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Messages\ApiMessages;
use Core\Models\eCCompany;
use Core\Models\eCCompanyConfig;
use Core\Models\eCDepartments;
use Core\Models\eCPositions;
use Core\Models\eCUser;
use Core\Models\eCUserSignature;
use Customer\Queues\PTApiLoginEvent;
use Customer\Services\eCCustomerService;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Core\Services\ActionHistoryService;

class eCCustomerLoginController extends eCBaseController
{

    private $customerService;
    private $actionHistoryService;

    /**
     * eCCustomerLoginController constructor.
     * @param $customerService
     */
    public function __construct(eCCustomerService $customerService, ActionHistoryService $actionHistoryService)
    {
        $this->customerService = $customerService;
        $this->actionHistoryService = $actionHistoryService;
    }


    public function login(Request $request)
    {
        try {
//            $code = $request->json("code");
            $email = $request->json("email");
            $password = $request->json("password");
            if (strlen($password) < 6)   return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::PASSWORD_SHORT, array(), ApiHttpStatus::VALIDATION);
            $rules = [
//                'code' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6'
            ];
            $validator = Validator::make(
                [
//                    'code' => $code,
                    'email' => $email,
                    'password' => $password
                ],
                $rules);
            if ($validator->fails()) {
                return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::VALIDATE_FAILED, array(), ApiHttpStatus::BAD_REQUEST);
            }
//            $code = $request->get('code');
//            $company = eCCompany::where('company_code', $code)->first();
//            if ($company == null) {
//                return $this->sendError(ApiHttpStatus::UNAUTHORIZED, 'Doanh nghiệp này không tồn tại', array(), ApiHttpStatus::UNAUTHORIZED);
//            }
            $credentials = [
//                'company_id' => $company->id,
                'email' => base64_encode( Str::lower($request->json('email'))),
                'password' => $request->json('password')
            ];
            if (Auth::attempt($credentials, true)) {
                $user = Auth::user();

                if ($user->status == 1) {
                    event(new PTApiLoginEvent($user->id));
                    $user['token'] = $user->createToken('eContract@2022')->accessToken;

                    //Get Oauth Access tokens moi nhat
                    $result = $this->customerService->updateAccessToken($user);
                    return $this->sendResponse($result, ApiMessages::AUTH_SUCCESS);
                } else {
                    return $this->sendError(ApiHttpStatus::NOT_ACCEPTABLE, ApiMessages::ACCOUNT_NOT_ACCEPTABLE, array(), ApiHttpStatus::NOT_ACCEPTABLE);
                }
            } else {
                return $this->sendError(ApiHttpStatus::PASSWORD_WRONG, ApiMessages::AUTH_FAILED, array(), ApiHttpStatus::PASSWORD_WRONG);
            }
        } catch (Exception $e) {
            Log::error("[LoginController][login] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, ApiMessages::ERROR_PROCESSING, $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function isLogin()
    {
        $is_login = Auth::check();
        return $this->sendResponse([
            'is_login' => $is_login
        ], ApiMessages::GET_INFO_LOGIN);

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

    public function initData() {
        $user = Auth::user();
        $userInfo = $this->customerService->getUserInfo($user);
        $initData = array();
        $initData["lstPosition"] = eCPositions::where('company_id', $user->company_id)->whereYear('created_at', date('Y'))->get();
        $initData["lstDepartment"] = eCDepartments::where('company_id', $user->company_id)->whereYear('created_at', date('Y'))->get();
        $configData= eCCompanyConfig::find($user->company_id);
        $signature = eCUserSignature::where('user_id', $user->id)->select('image_signature')->first();

        $userInfo["signature"] = isset($signature) ? $signature->image_signature : null;

        $firstLogin = false;
        if($user["is_first_login"] == true) {
            $firstLogin = $this->customerService->updateFirstLogin($user);
        }

        // $permissions = DB::select("SELECT rp.permission_id, rp.is_view, rp.is_write, rp.is_approval, rp.is_decision, p.permission FROM ec_s_role_permission rp JOIN ec_s_permissions p ON rp.permission_id = p.id where role_id = ?", array($user->role_id));

        return $this->sendResponse(['user' => $userInfo, 'initData' => $initData, 'firstLogin' => $firstLogin, 'configData' => $configData], ApiMessages::INITIALIZATION_SUCCESS);
    }

    public function initDashboardData(){
        $user = Auth::user();
        $initData = array();
        $initData["company"] = eCCompany::where('id', $user->company_id)->select('name')->get();
        $initData["lstDocument"] = DB::select("SELECT COUNT(*) as total, document_type, document_state, MONTH(created_at) AS month FROM ec_documents WHERE company_id = ? AND YEAR(created_at) = YEAR(CURDATE()) AND delete_flag = 0 AND parent_id = -1 GROUP BY document_type, document_state, MONTH(created_at)",[$user->company_id]);

        return $this->sendResponse(['initData' => $initData], ApiMessages::INITIALIZATION_SUCCESS);
    }

    public function changePwd(Request $request) {
        $user = Auth::user();
        $oldPass = $request->json('old');
        $newPass = $request->json('new');
        $raw_log = [
            'oldPass' => $oldPass,
            'newPass' => $newPass,
        ];
        if (!Hash::check($oldPass, $user->password)) {
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'Mật khẩu cũ không chính xác', [], ApiHttpStatus::BAD_REQUEST);
        }
        try {
            eCUser::find($user->id)->update(['password' => Hash::make($newPass)]);

            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::USER_ACTION,'ec_users', 'UPDATE_USER',$user->name . ' đổi mật khẩu', json_encode($raw_log));
            return $this->sendResponse([], 'Đổi mật khẩu thành công!');
        } catch (Exception $e) {
            Log::error("[eCCustomerLoginController][changePwd] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function changeUserInfo(Request $request) {
        try{
            $user = Auth::user();
            $id = $request->json("id");
            $name = $request->json("name");
            $dob = $request->json("birthday");
            $sex = $request->json("sex");
            $phone = $request->json("phone");
            $address = $request->json("address");
            $language = $request->json('language');
            $rules = [
                'name' => 'required|max:255',
                'dob' => 'date',
                'sex' => 'in:0,1,2',
                'address' => 'max:255',
                'phone' => 'required|digits_between:9,15'
            ];

            $postData = [
                'name' => $name,
                'dob' => $dob,
                'sex' => $sex,
                'phone' => $phone,
                'address' => $address,
                'language' => $language
            ];
            $raw_log = $postData;

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'Dữ liệu nhập vào không hợp lệ', $validator->errors(), ApiHttpStatus::BAD_REQUEST);
            }

            if ($user->id != $id) {
                return $this->sendError(ApiHttpStatus::FORBIDDEN, 'Không có quyền cập nhật thông tin cá nhân', [], ApiHttpStatus::FORBIDDEN);
            }
            
            eCUser::find($id)->update($postData);

            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::USER_ACTION,'ec_users', 'UPDATE_USER',$user->name . ' thay đổi thông tin cá nhân', json_encode($raw_log));
            $newUser = DB::select("SELECT id, company_id, address, branch_id, sex, language, dob, role_id, is_first_login, FROM_BASE64(name) as name, FROM_BASE64(phone) as phone, FROM_BASE64(email) as email FROM ec_users WHERE id = ?", [$id]);
            // $newUser = DB::table('ec_users')->where('id', $id)->select('*', 'FROM_BASE64(name) as name', 'FROM_BASE64(phone) as phone')->first();
            $userInfo = $this->customerService->getUserInfo($newUser[0]);

            return $this->sendResponse(['user' => $userInfo], 'Cập nhật thành công!');
        } catch (Exception $e) {
            Log::error("[eCCustomerLoginController][changeUserInfo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function updateUserSignature(Request $request) {
        try {
            $image_signature = $request->json("image_signature");

            $result = $this->customerService->updateUserSignature($image_signature);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCustomerLoginController][updateUserSignature] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function deleteUserSignature(Request $request) {
        try {

            $result = $this->customerService->deleteUserSignature();
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCCustomerLoginController][updateUserSignature] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
