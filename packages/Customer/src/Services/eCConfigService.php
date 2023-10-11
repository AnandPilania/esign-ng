<?php


namespace Customer\Services;


use Core\Models\eCBranch;
use Core\Models\eCCompanyConversationTemplates;
use Core\Models\eCCompanySignature;
use Core\Models\eCConfigParams;
use Core\Models\eCConversationTemplates;
use Core\Models\eCPermissions;
use Core\Models\eCRolePermission;
use Core\Models\eCSendMail;
use Core\Models\eCSendSms;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Exception;
use Core\Models\eCUser;
use Core\Models\eCRole;
use Core\Models\eCCompany;
use Core\Models\eCCompanyConsignee;
use Core\Models\eCCompanyRemoteSign;
use Core\Models\eCDocumentTutorial;
use Core\Helpers\StorageHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Core\Services\ActionHistoryService;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
class eCConfigService
{
    private $storageHelper;
    private $permissionService;
    private $actionHistoryService;

    /**
     * eCUtilitiesService constructor.
     * @param $permissionService
     * @param ActionHistoryService $actionHistoryService
     */
    public function __construct(eCPermissionService $permissionService, ActionHistoryService $actionHistoryService, StorageHelper $storageHelper)
    {
        $this->storageHelper = $storageHelper;
        $this->permissionService = $permissionService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function initUserSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_USERS");

        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $lstRole = eCRole::where('company_id', $user->company_id)->where('status', 1)->get();
        $lstBranch = eCBranch::where('company_id', $user->company_id)->where('status', 1)->get();

        return array('lstRole' => $lstRole, 'permission' => $permission, 'lstBranch' => $lstBranch);
    }

    public function searchUser($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_USERS", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT eu.id, eu.company_id, FROM_BASE64(eu.name) as name, FROM_BASE64(eu.email) as email, eu.password, eu.role_id, er.role_name, eu.status, eu.is_personal, eu.language, eu.branch_id, b.name as branch_name FROM ec_users as eu JOIN ec_s_roles as er on eu.role_id = er.id LEFT JOIN ec_branches b ON eu.branch_id = b.id WHERE eu.company_id = ? AND eu.delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_users WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (FROM_BASE64(eu.name) LIKE ? OR FROM_BASE64(eu.email) LIKE ?)';
            $strCount .= ' AND (FROM_BASE64(name) LIKE ? OR FROM_BASE64(email) LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["role_id"] != -1) {
            $str .= ' AND eu.role_id = ?';
            $strCount .= ' AND role_id = ?';
            array_push($arr, $searchData["role_id"]);
        }

        if ($searchData["branch_id"] != -1) {
            $str .= ' AND eu.branch_id = ?';
            $strCount .= ' AND branch_id = ?';
            array_push($arr, $searchData["branch_id"]);
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND eu.status = ?';
            $strCount .= ' AND status = ?';
            array_push($arr, $searchData["status"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertUser($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_USERS", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'note' => 'max:512',
            'role_id' => 'required|max:10|exists:ec_s_roles,id',
            'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'status' => 'boolean',
            'is_personal' => 'boolean',
            'language' => 'in:vi,en'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
        $raw_log = $postData;
        $postData['email'] = Str::lower($postData['email']);

        $existedUser = eCUser::where('email', base64_encode($postData['email']))->first();
        if ($existedUser) throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');

        $postData['is_first_login'] = true;
        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;
        $postData['password'] = Hash::make($postData['password']);
        try {
            DB::beginTransaction();

            eCUser::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_users', 'INSERT_USER', $postData['name'], json_encode($raw_log));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateUser($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_USERS", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            // 'company_id' => 'required|max:11|exists:ec_companies,id',
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'note' => 'max:512',
            'role_id' => 'required|max:10|exists:ec_s_roles,id',
            // 'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'status' => 'boolean',
            'is_personal' => 'boolean',
            'language' => 'in:vi,en'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
        $raw_log = $postData;
        $postData['email'] = Str::lower($postData['email']);
        $editUser = eCUser::find($id);
        if (!$editUser) throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_USER');
        if ($editUser->company_id != $user->company_id)  throw new eCBusinessException('CONFIG.USER.NOT_PERMISSION_UPDATE');

        $existedUser = eCUser::where('id', '!=', $id)
            ->where('email', base64_encode($postData['email']))
            ->first();
        if ($existedUser) throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');
        $postData["updated_by"] = $user->id;
        try {
            DB::beginTransaction();

            $editUser->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_users', 'UPDATE_USER', $postData['name'], json_encode($raw_log));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function changePasswordUser($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_USERS", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $editUser = eCUser::find($id);
        if (!$editUser) throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_USER');
        if ($editUser->company_id != $user->company_id) throw new eCBusinessException('CONFIG.USER.NOT_PERMISSION_UPDATE');

        $existedUser = eCUser::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where('email', base64_encode($postData['email']))
            ->first();
        if ($existedUser) throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');
        $password['password'] = $postData['password'];
        $postData['password'] = Hash::make($postData['password']);
        $postData['email'] = Str::lower($postData['email']);
        try {
            DB::beginTransaction();

            $editUser->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_users', 'UPDATE_USER_PASSWORD', $editUser->name, json_encode($password));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteUserSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_USERS", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_users =eCUser::select('id','name')->whereIn('id', $ids)->where('company_id', $company_id)->get();
        $i = 1;
        foreach ($avail_users as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->name;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_USER');

        try {
            DB::beginTransaction();
            eCUser::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_users', 'DELETE_USER',  $count, json_encode($avail_rm));

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initPermissionSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_PERMISSION");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        return array('permission' => $permission);
    }

    public function initDetailPermissionSetting($id)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_PERMISSION");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        if ($id) {
            $role = eCRole::find($id);
            if (!$role) {
                throw new eCBusinessException('CONFIG.PERMISSION.NOT_EXISTED_PERMISSION');
            }
            if ($role->company_id != $user->company_id) {
                throw new eCBusinessException('CONFIG.PERMISSION.NOT_PERMISSION_UPDATE');
            }

            $role->permission = eCRolePermission::where('role_id', $id)->get();
        } else {
            $role = null;
        }
        $lstPermission = eCPermissions::where('status', 1)->get();
        return array('permission' => $permission, 'role' => $role, 'lstPermission' => $lstPermission);
    }

    public function searchPermission($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_PERMISSION", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT id, role_name, note, status FROM ec_s_roles WHERE company_id = ? AND delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_roles WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND role_name LIKE ? ';
            $strCount .= ' AND role_name LIKE ?';
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND status = ?';
            $strCount .= ' AND status = ?';
            array_push($arr, $searchData["status"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertPermission($postData, $lstPermission)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_PERMISSION", false, true, false, false);
            if (!$hasPermission) throw new eCAuthenticationException();

            $rules = [
                'role_name' => 'required|max:50',
                'note' => 'max:512',
                'status' => 'boolean'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $raw_log = $postData;

            $existedRole = eCRole::where('company_id', $user->company_id)
                ->where('role_name', $postData['role_name'])
                ->first();

            if ($existedRole) throw new eCBusinessException('CONFIG.PERMISSION.EXISTED_NAME');

            $postData["company_id"] = $user->company_id;
            $postData["created_by"] = $user->id;
            $postData["updated_by"] = $user->id;

            $role = eCRole::create($postData);
            $role->rolePermission()->createMany($lstPermission);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_roles', 'INSERT_ROLE', $postData['role_name'], json_encode($raw_log));
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_role_permission', 'INSERT_ROLE_PERMISSION', $postData['role_name'], json_encode($lstPermission));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updatePermission($id, $postData, $lstPermission)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_PERMISSION", false, true, false, false);
            if (!$hasPermission) throw new eCAuthenticationException();

            $rules = [
                'role_name' => 'required|max:50',
                'note' => 'max:512',
                'status' => 'boolean'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

            $permission = eCRole::find($id);
            if (!$permission) throw new eCBusinessException('CONFIG.PERMISSION.NOT_EXISTED_PERMISSION');

            if ($permission->company_id != $user->company_id) throw new eCBusinessException('CONFIG.PERMISSION.NOT_PERMISSION_UPDATE');
            $raw_log = $postData;

            $existedRole = eCRole::where('company_id', $user->company_id)
                ->where('id', '!=', $id)
                ->where('role_name', $postData['role_name'])
                ->first();

            if ($existedRole) throw new eCBusinessException('CONFIG.PERMISSION.EXISTED_NAME');

			$postData["company_id"] = $user->company_id;
	        $postData["created_by"] = $user->id;
	        $postData["updated_by"] = $user->id;

            $permission->update($postData);
            $permission->rolePermission()->delete();
            $permission->rolePermission()->createMany($lstPermission);

            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_roles', 'UPDATE_ROLE', $postData['role_name'], json_encode($raw_log));
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_role_permission', 'UPDATE_ROLE_PERMISSION', $postData['role_name'], json_encode($lstPermission));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            Log::error("[eCConfigController][updatePermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            throw $e;
        }
    }

    public function deletePermissionSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_PERMISSION", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_permissions = eCRole::select('id')
            ->whereIn('id', $ids)->where('company_id', $company_id)->get();
        $i = 1;
        foreach ($avail_permissions as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.PERMISSION.NOT_EXISTED_PERMISSION');
        $isUsingPermissions = eCUser::whereIn('role_id', $ids)->get();
        $using_ids = [];
        foreach ($isUsingPermissions as $p) {
            $using_ids[] = $p->permission_id;
        }
        // All permissions are using. Need to remove employees first.
        if (count($using_ids) == count($avail_ids)) throw new eCBusinessException('SERVER.IN_USE_DATA');
        $rm_ids = array_diff($avail_ids, $using_ids);
        $count = count($rm_ids);
        $avail_per = eCRole::select('role_name')
            ->whereIn('id', $rm_ids)->where('company_id', $company_id)->get();
        foreach ($avail_per as $per){
            $rm[$i++] = $per->role_name;
        }
        try {
            DB::beginTransaction();
            eCRole::whereIn('id', $rm_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_roles', 'DELETE_ROLE', $count, json_encode($rm));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initCompanySetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_ACCOUNT");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $account = eCCompany::where('id', $user->company_id)->where('status', 1)->first();
        $lstCompanyConsignee = eCCompanyConsignee::where('company_id', $user->company_id)->where('status', 1)->get();
        foreach ($lstCompanyConsignee as $company_consignee) {
            if ($company_consignee->role == 0) {
                $company_consignee->role_name = 'Phê duyệt tài liệu';
            } else if ($company_consignee->role == 1) {
                $company_consignee->role_name = 'Ký tài liệu';
            }
        }
        $remoteSign = eCCompanyRemoteSign::where('company_id', $user->company_id)->where('status', 1)->first();
        $companySignature = eCCompanySignature::where('company_id', $user->company_id)->first();

        return array('account' => $account, 'lstCompanyConsignee' => $lstCompanyConsignee, 'remoteSign' => $remoteSign, 'companySignature' => $companySignature, 'permission' => $permission);
    }

    public function updateCompany($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|max:255',
            'phone' => 'max:30',
            'tax_number' => 'max:30',
            'fax_number' => 'max:50',
            'bank_number' => 'max:250',
            'bank_info' => 'max:500',
            'contact_name' => 'max:250',
            'contact_phone' => 'max:50',
            'contact_email' => 'nullable|email|max:255',
            'representative' => 'max:30',
            'representative_position' => 'max:30',
            'website' => 'max:30',
            'sign_type' => 'boolean',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $account = eCCompany::find($id);
        if (!$account) throw new eCBusinessException('CONFIG.ACCOUNT.NOT_EXISTED_ACCOUNT');

        if ($account->id != $user->company_id) throw new eCBusinessException('CONFIG.ACCOUNT.NOT_PERMISSION_UPDATE');
        $raw_log = $postData;
        // // chỗ này kiểm tra lại nghiệp vụ sau
        $existedAccount = eCCompany::where('id', '!=', $id)
            ->where(function ($query) use ($postData) {
                $query->where('email', $postData["email"])
                    ->orWhere('phone', $postData["phone"]);
            })
            ->first();
        if ($existedAccount) throw new eCBusinessException('CONFIG.ACCOUNT.EXISTED');

        $postData['email'] = Str::lower($postData['email']);
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();
            $account->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_companies', 'UPDATE_COMPANY', "", json_encode($raw_log));
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function searchCompanyConsignee($searchData, $draw, $start, $limit, $sortQuery){
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $str = 'SELECT id, name, email, phone, role, status FROM ec_company_consignees WHERE company_id = ? AND delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_company_consignees WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND name LIKE ? OR email LIKE ? OR phone LIKE ? ';
            $strCount .= ' AND name LIKE ? OR email LIKE ? OR phone LIKE ? ';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["role_id"] != -1) {
            $str .= ' AND role = ?';
            $strCount .= ' AND role = ?';
            array_push($arr, $searchData["role_id"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertCompanyConsignee($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", false, true, false, false);
        if (!$hasPermission)  throw new eCAuthenticationException();

        $rules = [
            'name' => 'required|max:125',
            'email' => 'required|email|max:255',
            'phone' => 'max:30',
            'role' => 'required|max:4',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $existedCompanyConsignee = eCCompanyConsignee::where('company_id', $user->company_id)
            ->where('email', $postData['email'])
            ->first();
        $raw_log = $postData;
        if ($existedCompanyConsignee) throw new eCBusinessException('CONFIG.ACCOUNT.EXISTED_CONSIGNEE_EMAIL');

        $postData['email'] = Str::lower($postData['email']);
        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;
        try {
            DB::beginTransaction();

            $company_consignee = eCCompanyConsignee::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_company_consignees', 'INSERT_COMPANY_CONSIGNEE', "", json_encode($raw_log));
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateCompanyConsignee($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'name' => 'required|max:125',
            'email' => 'required|email|max:255',
            'phone' => 'max:30',
            'role' => 'required|max:4',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $company_consignee = eCCompanyConsignee::find($id);
        if (!$company_consignee) throw new eCBusinessException('CONFIG.ACCOUNT.NOT_EXISTED_CONSIGNEE');

        if ($company_consignee->company_id != $user->company_id) throw new eCBusinessException('CONFIG.ACCOUNT.CONSIGNEE_NOT_PERMISSION_UPDATE');

        $existedCompanyConsignee = eCCompanyConsignee::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where('email', $postData['email'])
            ->first();
        $raw_log = $postData;
        if ($existedCompanyConsignee) throw new eCBusinessException('CONFIG.ACCOUNT.EXISTED_CONSIGNEE_EMAIL');

        $postData['email'] = Str::lower($postData['email']);
        $postData["company_id"] = $user->company_id;
        $postData["updated_by"] = $user->id;
        try {
            DB::beginTransaction();

            $company_consignee ->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_company_consignees', 'UPDATE_COMPANY_CONSIGNEE', "", json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteCompanyConsigneeSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_users =  eCCompanyConsignee::select('id','name')
            ->whereIn('id', $ids)->where('company_id', $company_id)->get();
        $i = 1;
        foreach ($avail_users as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->name;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.ACCOUNT.NOT_EXISTED_CONSIGNEE');

        try {
            DB::beginTransaction();
            eCCompanyConsignee::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_company_consignees', 'DELETE_COMPANY_CONSIGNEE', 'Xóa ' . $count . ' người giao kết', json_encode($avail_rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateCompanyRemoteSign($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'provider' => 'required|max:255',
            'service_signing' => 'required|max:255',
            'login' => 'required|max:255',
            'password' => 'required|max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $remote_sign = eCCompanyRemoteSign::find($id);
        if (!$remote_sign) throw new eCBusinessException('CONFIG.ACCOUNT.NOT_EXISTED_REMOTE_SIGN');
        if ($remote_sign->company_id != $user->company_id) throw new eCBusinessException('CONFIG.ACCOUNT.REMOTE_SIGN_NOT_PERMISSION_UPDATE');

        $existedCompanyRemoteSign = eCCompanyRemoteSign::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->first();
        $raw_log = $postData;
        if ($existedCompanyRemoteSign) throw new eCBusinessException('CONFIG.ACCOUNT.EXISTED_REMOTE_SIGN');

        $postData["company_id"] = $user->company_id;
        $postData["updated_by"] = $user->id;
        try {
            DB::beginTransaction();

            $remote_sign->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_company_remote_sign', 'UPDATE_COMPANY_REMOTE_SIGN', "", json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateCompanySignature($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_ACCOUNT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $signature = eCCompanySignature::find($id);
        if (!$signature) throw new eCBusinessException('CONFIG.ACCOUNT.NOT_EXISTED_SIGNATURE');
        if ($signature->company_id != $user->company_id) throw new eCBusinessException('CONFIG.ACCOUNT.SIGNATURE_NOT_PERMISSION_UPDATE');

        $raw_log = $postData;
        $postData["company_id"] = $user->company_id;
        $postData["updated_by"] = $user->id;
        try {
            DB::beginTransaction();

            $signature->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_company_signature', 'UPDATE_COMPANY_SIGNATURE', "", json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initSendDocumentSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_SEND_DOC");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $params = eCConfigParams::where('company_id', $user->company_id)->first();
        $email = eCSendMail::where('company_id', $user->company_id)->first();
        $sms = eCSendSms::where('company_id', $user->company_id)->first();
        // $email->email_password = "";
        // $sms->sms_password = "";

        return array('permission' => $permission, 'params' => $params, 'email' => $email, 'sms' => $sms);
    }

    public function updateSendTime($postData)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_SEND_DOC");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $rules = [
//            'send_email_remind_day' => 'required|numeric',
            'document_expire_day' => 'required|numeric',
            'near_expired_date' => 'required|numeric',
            'near_doc_expired_date' => 'required|numeric',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
        $raw_log = $postData;

        $postData["updated_at"] = date('Y-m-d H:i:s');
        try {
            DB::beginTransaction();
            $params = eCConfigParams::where('company_id', $user->company_id)->first();
            if (!$params) {
                $postData["company_id"] = $user->company_id;
                eCConfigParams::create($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_config_params', 'INSERT_SEND_TIME', "", json_encode($raw_log));
            } else {
                $params->update($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_config_params', 'UPDATE_SEND_TIME', "", json_encode($raw_log));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateEmail($postData)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_SEND_DOC");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        $rules = [
            'email_address' => 'required|email',
            'email_host' => 'required|max:255',
            'email_protocol' => 'required',
            'email_name' => 'max:255',
            'port' => 'required|numeric|max:65535|min:0',
            'status' => 'boolean',
            'is_use_ssl' => 'boolean',
            'is_relay' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
        $raw_log = $postData;
        $existedPass = eCSendMail::where('id', $postData['id'])
            ->where('email_password', $postData['email_password'])
            ->first();
        $postData['email_address'] = Str::lower($postData['email_address']);
        $postData["updated_at"] = date('Y-m-d H:i:s');
        $postData["updated_by"] = $user->id;

        //TODO: check thong tin email chinh xac
        if(!$existedPass){
            $encrypt_password = Crypt::encryptString($postData['email_password']);
            $postData["email_password"] = $encrypt_password;
        }
        try {
            DB::beginTransaction();
            $email = eCSendMail::where('company_id', $user->company_id)->first();
            if (!$email) {
                $postData["company_id"] = $user->company_id;
                $postData["created_by"] = $user->id;
                eCSendMail::create($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_send_email', 'INSERT_SEND_EMAIL', "", json_encode($raw_log));
            } else {
                $email->update($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_send_email', 'UPDATE_SEND_EMAIL', "", json_encode($raw_log));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateSms($postData)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_SEND_DOC");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $rules = [
            'service_provider' => 'required|max:255',
            'service_url' => 'required|max:255',
            'brandname' => 'max:255',
            'sms_account' => 'required|max:255',
            'sms_password' => 'required',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
        $raw_log = $postData;
        $existedPass = eCSendSms::where('id', $postData['id'])
            ->where('sms_password', $postData['sms_password'])
            ->first();
        $postData["updated_at"] = date('Y-m-d H:i:s');
        $postData["updated_by"] = $user->id;

        //TODO: check tai khoan sms chinh xac
        if (!$existedPass) {
            $encrypt_password = Crypt::encryptString($postData['sms_password']);
            $postData["sms_password"] = $encrypt_password;
        }
        try {
            DB::beginTransaction();
            $sms = eCSendSms::where('company_id', $user->company_id)->first();
            if (!$sms) {
                $postData["company_id"] = $user->company_id;
                $postData["created_by"] = $user->id;
                eCSendSms::create($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_send_sms', 'INSERT_SEND_SMS', "", json_encode($raw_log));
            } else {
                $sms->update($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_send_sms', 'UPDATE_SEND_SMS', "", json_encode($raw_log));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initTemplateSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_TEMPLATE");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $lstTemplate = eCConversationTemplates::where('status', 1)->where('is_ams', 1)->get();

        return array('lstTemplate' => $lstTemplate, 'permission' => $permission);
    }

    public function searchTemplate($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_TEMPLATE", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $arr = array();
        $str = 'SELECT ct.id, cct.id as company_template_id, ct.type, cct.company_id, ct.template_description, ct.template AS system_template, cct.template AS company_template, ct.status, cct.status AS ct_status FROM ec_s_conversation_templates ct LEFT JOIN ec_s_company_conversation_templates cct ON ct.id = cct.template_id AND cct.company_id = ? AND cct.delete_flag = 0 WHERE ct.`status` = 1 and ct.is_ams = 1 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_conversation_templates ct LEFT JOIN ec_s_company_conversation_templates cct ON ct.id = cct.template_id AND cct.company_id = ? AND cct.delete_flag = 0 WHERE ct.`status` = 1 and ct.is_ams = 1 ';
        array_push($arr, $user->company_id);
        if (!empty($searchData["keyword"])) {
            $str .= ' AND ct.template_description LIKE ? ';
            $strCount .= ' AND ct.template_description LIKE ? ';
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["type"] != -1) {
            $str .= ' AND ct.type = ?';
            $strCount .= ' AND ct.type = ?';
            array_push($arr, $searchData["type"]);
        }
        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function updateTemplate($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_TEMPLATE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'template_id' => 'required|max:20',
            'status' => 'boolean',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
        $template_name = eCConversationTemplates::select('template_description')->whereId($postData['template_id'])->first();
        $raw_log = $postData;

        $postData["company_id"] = $user->company_id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();
            $editTemplate = eCCompanyConversationTemplates::find($id);
            if (!$editTemplate) {
                $postData["created_by"] = $user->id;
                $newTemplate = eCCompanyConversationTemplates::create($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_company_conversation_templates', 'INSERT_TEMPLATE', $template_name->template_description, json_encode($raw_log));
            } else {
                if ($editTemplate->company_id != $user->company_id) throw new eCBusinessException('CONFIG.TEMPLATE.NOT_PERMISSION_UPDATE');
                $editTemplate->update($postData);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_company_conversation_templates', 'UPDATE_TEMPLATE', $template_name->template_description, json_encode($raw_log));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteTemplateSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "CONFIG_TEMPLATE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_users = eCConversationTemplates::select('id','template_description')
            ->whereIn('id', $ids)->where('company_id', $company_id)->get();
        $i = 1;
        foreach ($avail_users as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->template_description;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_TEMPLATE');

        try {
            DB::beginTransaction();
            eCConversationTemplates::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::CONFIG_ACTION,'ec_s_company_conversation_templates', 'DELETE_TEMPLATE', $count, json_encode($avail_rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

}
