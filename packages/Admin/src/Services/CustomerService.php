<?php


namespace Admin\Services;


use Admin\Services\Shared\NotificationService;
use Carbon\Carbon;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\Common;
use Core\Helpers\NotificationType;
use Core\Models\eCAgencies;
use Core\Models\eCCompany;
use Core\Models\eCCompanyConfig;
use Core\Models\eCPermissions;
use Core\Models\eCRole;
use Core\Models\eCService;
use Core\Models\eCUser;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Core\Models\eCAdmin;
use Customer\Services\eCConfigService;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Core\Services\ActionHistoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerService
{
    private $notificationService;
    private $actionHistoryService;

    /**
     * eCUtilitiesService constructor.
     * @param NotificationService $notificationService
     * @param ActionHistoryService $actionHistoryService
     */
    public function __construct(NotificationService $notificationService, ActionHistoryService $actionHistoryService)
    {
        $this->notificationService = $notificationService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function init()
    {
        $user = Auth::user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }
        if($user->role_id == 1){
            $lstAgency = eCAgencies::where("status", 1)->get();
        } else if($user->role_id == 2){
            $lstAgency = eCAgencies::where("status", 1)->where('id', $user->agency_id)->get();
        }
        $lstService = eCService::where("status", 1)->get();
        return array('lstAgency' => $lstAgency, 'lstService' => $lstService);
    }

    public function search($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }

        $str = 'SELECT d.id , d.agency_id, d.service_id, d.source_method, name, d.company_code, d.tax_number, d.fax_number, d.address, d.phone, d.email, d.representative, d.total_doc, d.expired_date, d.status, FROM_BASE64(a.agency_name) as agency_name  FROM ec_companies d join ec_agencies a on a.id = d.agency_id WHERE d.delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_companies d WHERE d.delete_flag = 0 ';
        $arr = array();

        if (!empty($searchData["keyword"])) {
            $str .= ' AND d.name LIKE ? ';
            $strCount .= ' AND d.name LIKE ? ';
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["agency_id"] != -1) {
            $str .= ' AND d.agency_id = ?';
            $strCount .= ' AND d.agency_id = ?';
            array_push($arr, $searchData["agency_id"]);
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND d.status = ?';
            $strCount .= ' AND d.status = ?';
            array_push($arr, $searchData["status"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);
        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function create($postData)
    {
        $user = Auth::user();

        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }

        $company_code = Common::randomString(8);

        $tax_existed =eCCompany::where('tax_number', $postData['tax_number'])->get();
        if(count($tax_existed) > 0){
            throw new eCBusinessException('CONFIG.CUSTOMER.ERR_EXISTED_TAX_NUMBER');
        }

        $email_existed = eCCompany::where('email', $postData['email'])->get();
        if(count($email_existed) > 0){
            throw new eCBusinessException('CONFIG.CUSTOMER.ERR_EXISTED_EMAIL');
        }
        $existedUser = eCUser::where('email', base64_encode($postData['email']))->first();
        if ($existedUser) {
            throw new eCBusinessException('CONFIG.USER.EXISTED_USER_EMAIL');
        }
        $serviceConfig = eCService::find($postData['service_id']);
        $doc_num = 0;
        if ($serviceConfig->expires_time == -1){
            $expired_time = null;
        } else {
            $expired_date = Carbon::now();
            $postData["expired_date"] = date('Y-m-d 23:59:59', strtotime($expired_date->addDays($serviceConfig->expires_time)));
        }
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        DB::beginTransaction();
        try {
            $company = eCCompany::create($postData);
            // create config company
            $company->config()->create([
                "theme_header_color" => "#206bc4",
                "theme_footer_color" => "#206bc4",
                "file_size_upload" => 5,
                "step_color" => "yellow",
                "name_app" => "Fcontract",
                "text_color" => "white",
                "logo_sign" => "blue",
                "logo_login" => "images/fcontract-logo.png",
                "logo_background" => "images/bg-white-login.png",
                "fa_icon" => "fcontract-favicon.png",
                "loading" => "images/loading.jpg",
                "logo_dashboard" => "images/fcontract-dashboard.png",
            ]);
            $roleId = $company->role()->create([
                "role_name" => $user->role_id == 1 ? "Administrator" : "Agency",
                "created_by" => $user->id,
                "updated_by" => $user->id
            ])->id;
            $lstPermission = eCPermissions::where('status', 1)->get();
            $lstRolePermission = array();
            foreach ($lstPermission as $permission) {
                if ($permission->parent_permission) {
                    $lstRolePermission[] = [
                        "permission_id" => $permission->id,
                        "is_view" => $permission->is_view == 1 ? 1 : NULL,
                        "is_write" => $permission->is_write == 1 ? 1 : NULL,
                        "is_approval" => $permission->is_approval == 1 ? 1 : NULL,
                        "is_decision" => $permission->is_decision == 1 ? 1 : NULL,
                    ];
                }
            }
            eCRole::find($roleId)->rolePermission()->createMany($lstRolePermission);
            $company->user()->create([
                "name" => $postData['representative'],
                "email" => Str::lower($postData['email']),
                "password" => Hash::make($postData['password']),
                "role_id" => $roleId,
                "created_by" => $user->id,
                "updated_by" => $user->id,
            ]);
            //TODO: sendmail
            if($postData['email']){
                $exts = array(
                    "ten_dang_nhap" => $postData['email'],
                    "mat_khau" => $postData['password']
                );

                $this->notificationService->sendNotificationApiAccount($postData['email'], "", NotificationType::CREATE_COMPANY, $exts, 1);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update($postData)
    {
        $user = Auth::user();

        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }
        $oldEmail = eCCompany::select('email')->where('id', $postData['id'])->first()->email;

        $tax_existed = eCCompany::where('id', '!=', $postData['id'])->where('tax_number', $postData['tax_number'])->get();
        if(count($tax_existed) > 0){
            throw new eCBusinessException('CONFIG.CUSTOMER.ERR_EXISTED_TAX_NUMBER');
        }

        $email_existed = eCCompany::where('id', '!=', $postData['id'])->where('email', $postData['email'])->get();
        if(count($email_existed) > 0){
            throw new eCBusinessException('CONFIG.CUSTOMER.ERR_EXISTED_EMAIL');
        }
        $company = eCCompany::find($postData['id']);

        if($company->service_id != $postData['service_id']){
            $serviceConfig = eCService::find($postData['service_id']);
            $doc_num = 0;
            if ($serviceConfig->expires_time == -1){
                $expired_time = null;
            } else {
                $expired_date = Carbon::now();
                $postData["expired_date"] = date('Y-m-d 23:59:59', strtotime($expired_date->addDays($serviceConfig->expires_time)));
            }
        }

        $postData["updated_by"] = $user->id;
        $company->update($postData);

        eCUser::where('email', base64_encode($oldEmail))
        ->where('company_id', $postData['id'])
        ->update([
            "email" => base64_encode($postData["email"]),
        ]);

        return true;
    }

    public function delete($ids = [])
    {
        $user = Auth::user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }

        $avail_customers = eCCompany::select('id')->whereIn('id', $ids)->get();
        foreach ($avail_customers as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.CUSTOMER.NOT_EXISTED_CUSTOMER');

        try {
            DB::beginTransaction();
            eCCompany::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function changePasswordCustomer($request)
    {
        $user = Auth::user();

        $companyId = $request -> json("id");
        $password = $request -> json("password");
        $email = $request -> json("email");

        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }

        $customer = eCCompany::find($companyId);
        if (!$customer) {
            throw new eCBusinessException('CONFIG.CUSTOMER.NOT_EXISTED_CUSTOMER');
        }

        eCUser::where('email', base64_encode($email))
        ->where('company_id', $companyId)
        ->update([
            "password" => Hash::make($password)
        ]);

        return true;
    }

    public function getServiceDetail($id){
        $user = Auth::user();
        $customer = eCCompany::find($id);
        if (!$customer) {
            throw new eCBusinessException('CONFIG.CUSTOMER.NOT_EXISTED_CUSTOMER');
        }
        $service = DB::select("SELECT s.service_name, s.service_type , c.total_doc , c.expired_date FROM ec_companies c JOIN s_service_config s on c.service_id = s.id WHERE c.delete_flag = 0 AND c.id = ? ", array($id));
        if($service[0]->total_doc != null){
            $service[0]->total_doc = Crypt::decryptString($service[0]->total_doc);
        }
        $totalDocument = DB::select("SELECT count(*) as cnt FROM ec_documents WHERE delete_flag = 0  AND parent_id = -1 AND company_id =? ", array($id));
        $totalDocumentCompleted = DB::select("SELECT count(*) as cnt FROM ec_documents WHERE delete_flag = 0  AND parent_id = -1 AND document_state > 7 AND company_id =? ", array($id));
        return array("totalDocument" => $totalDocument[0]->cnt, "totalDocumentCompleted" => $totalDocumentCompleted[0]->cnt, 'service' => $service[0]);
    }

    public function searchDocumentList($searchData, $draw, $start, $limit, $sortQuery){
        $user = Auth::user();

        $arr = array();
        $str = 'SELECT distinct(d.id), d.company_id, d.sent_date, d.finished_date, d.code, d.created_by, a.full_name FROM ec_documents d JOIN ec_document_assignees a ON d.id = a.document_id AND a.assign_type = 0';
        $strCount = 'SELECT count(*) as cnt FROM ec_documents d JOIN ec_document_assignees a ON d.id = a.document_id AND a.assign_type = 0';

        $str .= " WHERE d.company_id = ? AND d.delete_flag = 0 AND d.parent_id = -1 ";
        $strCount .= " WHERE d.company_id = ? AND d.delete_flag = 0 AND d.parent_id = -1 ";
        array_push($arr, $searchData["company_id"]);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            $strCount .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if($searchData["state"] != -1){
            $str .= ' AND document_state > 7';
            $strCount .= ' AND document_state > 7';
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
            $strCount .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND d.sent_date >= ? ';
            $strCount .= ' AND d.sent_date >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND d.sent_date <= ?';
            $strCount .= ' AND d.sent_date <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $str .= " ORDER BY d." . $sortQuery . ', id desc';

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }


    public function getDataConfigCompany($id){
        $user = Auth::user();
        $customer = eCCompany::find($id);
        if (!$customer) {
            throw new eCBusinessException('CONFIG.CUSTOMER.NOT_EXISTED_CUSTOMER');
        }
        $configCompany = eCCompanyConfig::where('company_id', $id)->first();

        return array("configCompany" => $configCompany);
    }

    public function updateConfigCompany($postData) {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            eCCompanyConfig::where("company_id", $postData['company_id'])->update($postData);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function reNewService($id){
        $user = Auth::user();
        $customer = eCCompany::find($id);
        if (!$customer) {
            throw new eCBusinessException('CONFIG.CUSTOMER.NOT_EXISTED_CUSTOMER');
        }

        DB::beginTransaction();
        try {
            $serviceConfig = eCService::find($customer->service_id);
            $customer->total_doc = 0;
            if ($serviceConfig->expires_time == -1){
                $expired_time = null;
            } else {
                $expired_date = Carbon::now();
                $customer->expired_date = date('Y-m-d 23:59:59', strtotime($expired_date->addDays($serviceConfig->expires_time)));
            }
            $customer->save();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
