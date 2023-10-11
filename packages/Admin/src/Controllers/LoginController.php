<?php


namespace Admin\Controllers;


use Admin\Queues\LoginEventQueue;
use Admin\Services\AdminService;
use Carbon\Carbon;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Models\eCAdmin;
use Core\Models\eCDocuments;
use Customer\Queues\PTApiLoginEvent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class LoginController extends eCBaseController
{

    private $userService;

    protected $guard = 'admin';

    /**
     * LoginController constructor.
     * @param AdminService $userService
     */
    public function __construct(AdminService $userService)
    {
        $this->userService = $userService;
    }

    public function guard()
    {
        return Auth::guard($this->guard);
    }

    public function login(Request $request)
    {
        try {
            $email = $request->json("email");
            $password = $request->json("password");
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ];
            $validator = Validator::make(['email' => $email, 'password' => $password], $rules);
            if ($validator->fails()) {
                return $this->sendError("422", 'Email đăng nhập không được để trống !', array(), ApiHttpStatus::BAD_REQUEST);
            }
            $email = Str::lower($request->json('email'));
            $credentials = ['email' => base64_encode($email), 'password' => $request->json('password')];
            if (!$token = auth('admin')->attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp])) {
                return $this->sendError("422", 'Mật khẩu đăng nhập không chính xác !', array(), ApiHttpStatus::PASSWORD_WRONG);
            }

            $user = auth('admin')->user();
            if ($user->status != 1) {
                return $this->sendError("422", 'Tài khoản chưa kích hoạt !', array(), ApiHttpStatus::NOT_ACCEPTABLE, ApiHttpStatus::OK);
            }

            event(new LoginEventQueue($user->id));

            //Step 2 : login
            $user['token'] = $token;

            //update last login
            $result = $this->userService->updateAccessToken($user);
            return $this->sendResponse($result, 'Đăng nhập thành công !');
        } catch (Exception $e) {
            Log::error("[LoginController][login] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError("500", "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function isLogin()
    {
        $is_login = auth('admin')->check();
        if ($is_login) {
            return $this->sendResponse([], 'Lấy thông tin đăng nhập');
        } else {
            return $this->sendError("", "Chưa đăng nhập hệ thống!");
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


    public function updateProfile(Request $request)
    {
        //update profile
        $user = Auth::user();
        $full_name = $request->json('full_name');
        $address = $request->json('address');

        eCAdmin::find($user->id)->update([
            'full_name' => $full_name,
            'address' => $address
        ]);
        return $this->sendResponse(1, 'Successfully');
    }

    public function changePwd(Request $request) {
        $user = auth('admin')->user();
        $oldPass = $request->json('old');
        $newPass = $request->json('new');
        if (!Hash::check($oldPass, $user->password)) {
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'Mật khẩu cũ không chính xác', [], ApiHttpStatus::BAD_REQUEST);
        }
        try {
            eCAdmin::find($user->id)->update(['password' => Hash::make($newPass)]);
            return $this->sendResponse([], 'Đổi mật khẩu thành công!');
        } catch (Exception $e) {
            Log::error("[LoginController][changePwd] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }

    public function initData() {
        $user = auth('admin')->user();
        $userInfo = $this->userService->getUserInfo($user);
        $first_Login = $user->is_first_login;
        $initData = array();
        if($first_Login != 1){
            try{
                eCAdmin::find($user->id)->update(['is_first_login' => 1]);
                DB::commit();
            }
            catch (Exception $e) {
                Log::error("[LoginController][initData] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, "Xảy ra lỗi trong quá trình thực hiện", $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
            }
        }
        return $this->sendResponse(['user' => $userInfo, 'initData' => $initData, 'first_Login' => $first_Login], 'Khởi tạo thành công');

    }

    public function initDashboardData(Request $request){
        $user = auth('admin')->user();
        $year = $request->dashboardYear;
        $initData = array();
        $isAgency = false;

        $str = 'SELECT Sum(d.price) AS total_price, COUNT(*) as total ,MONTH(d.created_at) AS month, d.company_id,c.service_id,c.agency_id FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id where d.delete_flag = 0  AND d.parent_id = -1 AND d.document_state > 1 AND YEAR(d.created_at) = ?  ';
        $companyStr = "SELECT c.id, c.name, c.agency_id, c.service_id, c.status, c.delete_flag, (SELECT SUM(d.price) FROM ec_documents d WHERE d.company_id = c.id AND d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1 AND YEAR(d.created_at) = 2023) AS total_price,(SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1 AND YEAR(d.created_at) = ?) AS total_doc_year,(SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1) AS total_doc FROM ec_companies c";

        $arr = array($year);
        if ($user->role_id != 1){
            $str .= " AND c.agency_id = ? ";
            $companyStr .= " WHERE agency_id = ? ";
            array_push($arr, $user->agency_id);
            $isAgency = true;
        }
        $str .= " GROUP BY MONTH(d.created_at), d.company_id ORDER BY  MONTH(d.created_at) ASC ";

        $documents = DB::select($str, $arr);
        $companies = DB::select($companyStr,$arr);
        $agencies = DB::select("SELECT a.id, FROM_BASE64(a.agency_name) AS agency_name, a.status, a.delete_flag,( SELECT COUNT(*) FROM ec_companies c WHERE c.agency_id = a.id AND c.delete_flag = 0) AS company FROM ec_agencies a");
        $totalDocument = 0;
        $totalPrice=0;
        $turnOver = array(0,0,0,0,0,0,0,0,0,0,0,0);
        if ($documents) {
            foreach ($documents as $document) {
                $turnOver[$document->month - 1] += $document->total_price;
                $totalPrice += $document->total_price;
                $totalDocument += $document->total;
            }
        }

        foreach ($agencies as $agency) {
            $agency->total_doc_year = 0;
            $agency->total_price = 0;
            foreach ($companies as $company) {
                if($agency->id == $company->agency_id) {
                    $agency->total_doc_year += $company->total_doc_year;
                    $agency->total_price += $company->total_price;
                }
            }
        }
        $initData["totalPrice"] =$totalPrice;
        $initData["totalDocument"] = $totalDocument;
        $initData["companies"] = $companies;
        $initData["agencies"] = $agencies;
        $initData["turnOver"] = $turnOver;
        $initData["isAgency"] = $isAgency;
        return $this->sendResponse(['initData' => $initData], 'Khởi tạo thành công');
    }
}
