<?php


namespace Customer\Controllers;


use Carbon\Carbon;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\DocumentType;
use Customer\Services\eCSearchService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class eCSearchController extends eCBaseController
{
    private $searchService;

    protected $guard = 'search';

    /**
     * eCDocumentAssigneeController constructor.
     * @param $searchService
     */
    public function __construct(eCSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function getProfile() {
        $searcher = auth('search')->user();
        return $this->sendResponse($searcher, 'Get profile');
    }

    public function login(Request $request)
    {
//        return Hash::make("Le0qMX");
        try {
            $email = $request->json("email");
            $password = $request->json("password");
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ];
            $validator = Validator::make([
                'email' => $email,
                'password' => $password,
            ], $rules);
            if ($validator->fails()) {
                return $this->sendError("422", 'Email đăng nhập không hợp lệ !', array(), ApiHttpStatus::BAD_REQUEST);
            }
            $credentials = [
                'email' => base64_encode( Str::lower($request->json('email')) ),
                'password' => $request->json('password'),
            ];

            if (!$token = auth('search')->attempt($credentials, ['exp' => Carbon::now()->addMinutes(30)->timestamp])) {
                return $this->sendError("422", 'Thông tin đăng nhập không chính xác!', array(), ApiHttpStatus::PASSWORD_WRONG);
            }

            $user = auth('search')->user();
            if ($user->status != 1) {
                return $this->sendError("422", 'Tài khoản chưa kích hoạt !', array(), ApiHttpStatus::NOT_ACCEPTABLE);
            }

            //Step 2 : login
            $user['token'] = $token;

            $result = $this->searchService->updateAccessToken($user);
            return $this->sendResponse($result, 'Đăng nhập thành công !');

        } catch (Exception $e) {
            Log::error("[eCSearchController][login] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError("500", "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function logout(array $guards = null) {
        $guards = $guards ?: array_keys(config('auth.guards'));

        foreach ($guards as $guard) {

            if ($guard instanceof \Illuminate\Auth\SessionGuard) {
                $guard->logout();
            }
        }
        return $this->sendResponse([], 'Đăng xuất thành công');
    }

    public function isLogin(Request $request)
    {
        $user = auth('search')->user();

        if (!$user) {
            return $this->sendError(['is_login' => false], "");
        }

        return $this->sendResponse([
            'is_login' => true
        ], 'Lấy thông tin đăng nhập');
    }

    public function getPassword(Request $request){
        try{
            $rules = [
                'email' => 'required|email',
            ];
            $validator = Validator::make([
                'email' => $request->json("email"),
            ], $rules);
            if ($validator->fails()) {
                return $this->sendError("422", 'Email đăng nhập không hợp lệ !', array(), ApiHttpStatus::BAD_REQUEST);
            }
            $email = $request->json("email");            
            $existAssignee = DB::table('ec_document_assignees')
            ->where('delete_flag', 0)
            ->where('email', $email)
            ->first();
            if($existAssignee){
                $existedUser = DB::table('ec_searchers')
                ->where('delete_flag', 0)
                ->where('email', base64_encode($email))
                ->first();
                if(!$existedUser){
                    $result = $this->searchService->getPassword($email);
                    return $this->sendResponse($result, "Hệ thống sẽ gửi mật khẩu qua email.");
                }
                return $this->sendResponse([], '');
            }
            else{
                return $this->sendError("422", 'Email không tồn tại trong hệ thống !', array(), ApiHttpStatus::BAD_REQUEST);
            }
        }
        catch(Exception $e){
            Log::error("[eCSearchController][getPassword] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }

    }

    public function forgetPassword(Request $request){
        try{
            $email = $request->json("email");
            $result = $this->searchService->forgetPassword($email);
            return $this->sendResponse($result, "Hệ thống sẽ gửi mật khẩu mới qua email.");
        }
        catch(Exception $e){
            Log::error("[eCSearchController][forgetPassword] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }

    }

    public function initData() {
        $user = auth('search')->user();
        $userInfo = $this->searchService->getUserInfo($user);
        $initData = array();
        $signature = DB::table('ec_searcher_signature')->where('searcher_id', $user->id)->select('image_signature')->first();
        $userInfo["signature"] = null;

        if ($signature) {
            $userInfo["signature"] = $signature->image_signature;
        }

        $firstLogin = false;
        if($user["is_first_login"] == true) {
            $firstLogin = $this->searchService->updateFirstLogin($user);
        }

        // $permissions = DB::select("SELECT rp.permission_id, rp.is_view, rp.is_write, rp.is_approval, rp.is_decision, p.permission FROM ec_s_role_permission rp JOIN ec_s_permissions p ON rp.permission_id = p.id where role_id = ?", array($user->role_id));

        return $this->sendResponse(['user' => $userInfo, 'firstLogin' => $firstLogin], 'Khởi tạo thành công');
    }

    public function changePwd(Request $request) {
        $user = auth('search')->user();
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
            DB::table('ec_searchers')
                ->where('id', $user->id)
                ->update(['password' => Hash::make($newPass)]);

            //$this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::USER_ACTION,'ec_users', 'UPDATE_USER',$user->name . ' đổi mật khẩu', json_encode($raw_log));
            return $this->sendResponse([], 'Đổi mật khẩu thành công!');
        } catch (Exception $e) {
            Log::error("[eCSearchController][changePwd] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function changeUserInfo(Request $request) {
        try{
            $user = auth('search')->user();
            $id = $request->json("id");
            $name = $request->json("name");
            $dob = $request->json("birthday");
            $sex = $request->json("sex");
            $phone = $request->json("phone");
            $address = $request->json("address");
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
            ];
            $raw_log = $postData;

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'Dữ liệu nhập vào không hợp lệ', $validator->errors(), ApiHttpStatus::BAD_REQUEST);
            }

            if ($user->id != $id) {
                return $this->sendError(ApiHttpStatus::FORBIDDEN, 'Không có quyền cập nhật thông tin cá nhân', [], ApiHttpStatus::FORBIDDEN);
            }


            $updatePosition = DB::table('ec_searchers')
                ->where('id', $id)
                ->update($postData);

            //$this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::USER_ACTION,'ec_users', 'UPDATE_USER',$user->name . ' thay đổi thông tin cá nhân', json_encode($raw_log));
            $newUser = DB::select("SELECT id, address, sex, language, dob, is_first_login, name, phone, FROM_BASE64(email) as email FROM ec_searchers WHERE id = ?", [$id]);
            // $newUser = DB::table('ec_users')->where('id', $id)->select('*', 'FROM_BASE64(name) as name', 'FROM_BASE64(phone) as phone')->first();
            $userInfo = $this->searchService->getUserInfo($newUser[0]);

            return $this->sendResponse(['user' => $userInfo], 'Cập nhật thành công!');
        } catch (Exception $e) {
            Log::error("[eCSearchController][changeUserInfo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function updateUserSignature(Request $request) {
        try {
            $image_signature = $request->json("image_signature");

            $result = $this->searchService->updateUserSignature($image_signature);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCSearchController][updateUserSignature] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initDocumentList()
    {
        try {
            $result = $this->searchService->initDocumentListSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCSearchController][initDocumentList] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchDocument(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->searchService->searchDocument($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCSearchController][searchDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initViewDocument(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->searchService->initViewDocument($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCSearchController][initViewDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getSignDocument(Request $request) {
        try {
            $id = $request->id;
            $result = $this->searchService->getSignDocument($id);
            return $result;
        } catch (Exception $e) {
            Log::error("[eCSearchController][getSignDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getHistoryDocument(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->searchService->getHistoryDocument($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCSearchController][getHistoryDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getHistoryTransactionDocument(Request $request) {
        try {
            $id = $request->docId;
            $result = $this->searchService->getHistoryTransactionDocument($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCSearchController][getHistoryTransactionDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}    