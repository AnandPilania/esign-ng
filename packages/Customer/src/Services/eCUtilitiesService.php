<?php


namespace Customer\Services;

use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Models\eCDocumentGroups;
use Core\Models\eCDocuments;
use Core\Models\eCUser;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Exception;
use Core\Models\eCPositions;
use Core\Models\eCDepartments;
use Core\Models\eCCompany;
use Core\Models\eCCompanyConsignee;
use Core\Models\eCCompanyRemoteSign;
use Core\Models\eCDocumentTypes;
use Core\Models\eCCustomers;
use Core\Models\eCEmployee;
use Core\Models\eCBranch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Core\Services\ActionHistoryService;

class eCUtilitiesService
{
    private $permissionService;
    private $actionHistoryService;
    /**
     * eCUtilitiesService constructor.
     * @param $permissionService
     */
    public function __construct(eCPermissionService $permissionService, ActionHistoryService $actionHistoryService)
    {
        $this->permissionService = $permissionService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function initPositionSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_POSITION");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        return array('permission' => $permission);
    }

    public function searchPosition($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_POSITION", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $str = 'SELECT id, name, position_code, note, status FROM ec_s_positions WHERE company_id = ? AND delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_positions WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (name LIKE ? OR position_code LIKE ?)';
            $strCount .= ' AND (name LIKE ? OR position_code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
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

    public function insertPosition($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_POSITION", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'position_code' => 'required|max:25',
            'name' => 'required|max:255',
            'note' => 'max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $existedPosition = eCPositions::where('company_id', $user->company_id)
            ->where(function ($query) use ($postData) {
                $query->where('position_code', $postData["position_code"])
                    ->orWhere('name', $postData["name"]);
            })
            ->first();
        if ($existedPosition) throw new eCBusinessException('UTILITES.POSITION.EXISTED_CODE_NAME');
        $raw_log = $postData;

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCPositions::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_positions', 'INSERT_POSITION', $postData['name'], json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updatePosition($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_POSITION", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'position_code' => 'required|max:25',
            'name' => 'required|max:255',
            'note' => 'max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $position = eCPositions::find($id);
        if (!$position) throw new eCBusinessException('UTILITES.POSITION.NOT_EXISTED_POSITION');

        if ($position->company_id != $user->company_id) throw new eCBusinessException('UTILITES.POSITION.NOT_PERMISSION');

        $existedPosition = eCPositions::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($postData) {
                $query->where('position_code', $postData["position_code"])
                    ->orWhere('name', $postData["name"]);
            })
            ->first();
        if ($existedPosition) throw new eCBusinessException('UTILITES.POSITION.EXISTED_CODE_NAME');

        try {
            DB::beginTransaction();

            $position->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_positions', 'UPDATE_POSITION', $position->name, json_encode($postData));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deletePositionSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_POSITION", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;

        $avail_positions = eCPositions::select('id','name')->whereIn('id', $ids)->where('company_id', $company_id)->get();

        foreach ($avail_positions as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('UTILITES.POSITION.NOT_EXISTED_POSITION');
        $isUsingPositions = eCEmployee::select('ec_s_employees.position_id')
            ->selectRaw('COUNT(ec_s_employees.id) as nr_employee')
            ->where('ec_s_employees.delete_flag', 0)
            ->whereIn('ec_s_employees.position_id', $ids)
            ->having('nr_employee', '>', 0)
            ->groupBy('ec_s_employees.position_id')
            ->get();
        $using_ids = [];
        foreach ($isUsingPositions as $p) {
            $using_ids[] = $p->position_id;
        }
        // All positions are using. Need to remove employees first.
        if (count($using_ids) == count($avail_ids)) throw new eCBusinessException('SERVER.IN_USE_DATA');
        $rm_ids = array_diff($avail_ids, $using_ids);
        $i = 1;
        $avail_per = eCPositions::select('name')->whereIn('id', $rm_ids)->where('company_id', $company_id)->get();
        foreach ($avail_per as $per){
            $rm[$i++] = $per->name;
        }
        $count = count($rm_ids);
        try {
            DB::beginTransaction();
            eCPositions::whereIn('id', $rm_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_positions', 'DELETE_POSITION', $count, json_encode($rm));

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initDepartmentSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_DEPARTMENT");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        return array('permission' => $permission);
    }

    public function searchDepartment($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DEPARTMENT", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT id, name, department_code, note, status FROM ec_s_departments WHERE company_id = ? AND delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_departments WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (name LIKE ? OR department_code LIKE ?)';
            $strCount .= ' AND (name LIKE ? OR department_code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
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

    public function insertDepartment($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DEPARTMENT", false, true, false, false);
        if (!$hasPermission)  throw new eCAuthenticationException();

        $rules = [
            'department_code' => 'required|max:25',
            'name' => 'required|max:255',
            'note' => 'max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails())throw new eCBusinessException('SERVER.INVALID_INPUT');

        $existedDepartment = eCDepartments::where('company_id', $user->company_id)
            ->where(function ($query) use ($postData) {
                $query->where('department_code', $postData["department_code"])
                    ->orWhere('name', $postData["name"]);
            })
            ->first();
        if ($existedDepartment) throw new eCBusinessException('UTILITES.DEPARTMENT.EXISTED_CODE_NAME');
        $raw_log = $postData;

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCDepartments::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_departments', 'INSERT_DEPARTMENT', $postData['name'], json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateDepartment($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DEPARTMENT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'department_code' => 'required|max:25',
            'name' => 'required|max:255',
            'note' => 'max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $department = eCDepartments::find($id);
        if (!$department) throw new eCBusinessException('UTILITES.DEPARTMENT.NOT_EXISTED_DEPARTMENT');

        if ($department->company_id != $user->company_id) throw new eCBusinessException('UTILITES.DEPARTMENT.NOT_PERMISSION');

        $existedDepartment = eCDepartments::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($postData) {
                $query->where('department_code', $postData["department_code"])
                    ->orWhere('name', $postData["name"]);
            })
            ->first();
        if ($existedDepartment) throw new eCBusinessException('UTILITES.DEPARTMENT.EXISTED_CODE_NAME');

        try {
            DB::beginTransaction();

            $department->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_departments', 'UPDATE_DEPARTMENT', $department->name, json_encode($postData));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteDepartmentSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DEPARTMENT", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_departments = eCDepartments::select('id')
            ->whereIn('id', $ids)->where('company_id', $company_id)->get();
        foreach ($avail_departments as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('UTILITES.DEPARTMENT.NOT_EXISTED_DEPARTMENT');
        $isUsingDepartments = eCEmployee::select('ec_s_employees.department_id')
            ->selectRaw('COUNT(ec_s_employees.id) as nr_employee')
            ->whereIn('ec_s_employees.department_id', $ids)
            ->having('nr_employee', '>', 0)
            ->groupBy('ec_s_employees.department_id')
            ->get();
        $using_ids = [];
        foreach ($isUsingDepartments as $p) {
            $using_ids[] = $p->department_id;
        }
        // All departments are using. Need to remove employees first.
        if (count($using_ids) == count($avail_ids)) {
            throw new eCBusinessException('SERVER.IN_USE_DATA');
        }
        $rm_ids = array_diff($avail_ids, $using_ids);
        $i = 1;
        $avail_per = eCDepartments::select('name')->whereIn('id', $rm_ids)->where('company_id', $company_id)->get();
        foreach ($avail_per as $per){
            $rm[$i++] = $per->name;
        }
        $count = count($rm_ids);

        try {
            DB::beginTransaction();
            eCDepartments::whereIn('id', $rm_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_departments', 'DELETE_DEPARTMENT', $count, json_encode($rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initCustomerSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_CUSTOMER");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        return array('permission' => $permission);
    }

    public function searchCustomer($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_CUSTOMER", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $str = 'SELECT id, name, code, tax_number, address, phone, email, bank_info, bank_account, bank_number, representative, representative_position, contact_name, contact_phone, /*customer_type,*/ note, status FROM ec_s_customers WHERE company_id = ? AND delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_customers WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (name LIKE ? OR code LIKE ? OR phone LIKE ? OR email LIKE ? OR tax_number LIKE ?)';
            $strCount .= ' AND (name LIKE ? OR code LIKE ? OR phone LIKE ? OR email LIKE ? OR tax_number LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND status = ?';
            $strCount .= ' AND status = ?';
            array_push($arr, $searchData["status"]);
        }

        // if ($searchData["customer_type"] != -1) {
        //     $str .= ' AND customer_type = ?';
        //     $strCount .= ' AND customer_type = ?';
        //     array_push($arr, $searchData["customer_type"]);
        // }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertCustomer($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_CUSTOMER", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $rules = [
            'code' => 'required|max:50',
            'name' => 'required|max:125',
            'address' => 'required|max:512',
            'phone' => 'max:30',
            'email' => 'required|email|max:255',
            // 'contact_name' => 'max:255',
            // 'contact_phone' => 'max:255',
            'bank_info' => 'max:255',
            // 'bank_account' => 'max:255',
            'bank_number' => 'max:255',
            'note' => 'max:255',
            'status' => 'boolean',
            'tax_number' => 'max:125'
            // 'customer_type' => 'boolean',
        ];

        $existedCustomer = eCCustomers::where('company_id', $user->company_id)
            ->where(function ($query) use ($postData) {
                $query->where('code', $postData["code"])
                ->orWhere('email', $postData["email"]);
            })
            ->first();
        $existedErr = 'UTILITES.CUSTOMER.ERR_EXISTED_CODE_EMAIL';

        // if ($postData['customer_type']) {
        //     $rules['tax_number'] = 'required|max:125';
        //     $rules['representative'] = 'required|max:255';
        //     $rules['representative_position'] = 'required|max:255';
        //     $existedCustomer = DB::table('ec_s_customers')
        //     ->where('company_id', $user->company_id)
        //     ->where('delete_flag', 0)
        //     ->where(function ($query) use ($postData) {
        //         $query->where('code', $postData["code"])
        //             ->orWhere('tax_number', $postData["tax_number"]);
        //     })
        //     ->first();
        //     $existedErr = 'UTILITES.CUSTOMER.ERR_EXISTED_CODE_TAX_NUMBER';
        // } else {
        //     $rules['tax_number'] = 'max:125';
        //     $rules['representative'] = 'max:255';
        //     $rules['representative_position'] = 'max:255';

        // }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        if ($existedCustomer) throw new eCBusinessException($existedErr);
        $raw_log = $postData;

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCCustomers::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_customers', 'INSERT_CUSTOMER', $postData['code'], json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateCustomer($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_CUSTOMER", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'code' => 'required|max:50',
            'name' => 'required|max:125',
            'address' => 'required|max:512',
            'phone' => 'max:30',
            'email' => 'required|email|max:255',
            // 'contact_name' => 'max:255',
            // 'contact_phone' => 'max:255',
            'bank_info' => 'max:255',
            // 'bank_account' => 'max:255',
            'bank_number' => 'max:255',
            'note' => 'max:255',
            'status' => 'boolean',
            'tax_number' => 'max:125'
            // 'customer_type' => 'boolean',
        ];

        $existedCustomer = eCCustomers::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($postData) {
                $query->where('code', $postData["code"])
                ->orWhere('email', $postData["email"]);
            })
            ->first();
        $existedErr = 'UTILITES.CUSTOMER.ERR_EXISTED_CODE_EMAIL';

        // if ($postData['customer_type']) {
        //     $rules['tax_number'] = 'required|max:125';
        //     $rules['representative'] = 'required|max:255';
        //     $rules['representative_position'] = 'required|max:255';
        //     $existedCustomer = DB::table('ec_s_customers')
        //     ->where('company_id', $user->company_id)
        //     ->where('delete_flag', 0)
        //     ->where('id', '!=', $id)
        //     ->where(function ($query) use ($postData) {
        //         $query->where('code', $postData["code"])
        //             ->orWhere('tax_number', $postData["tax_number"]);
        //     })
        //     ->first();
        //     $existedErr = 'UTILITES.CUSTOMER.ERR_EXISTED_CODE_TAX_NUMBER';
        // } else {
        //     $rules['tax_number'] = 'max:125';
        //     $rules['representative'] = 'max:255';
        //     $rules['representative_position'] = 'max:255';
        //     $existedCustomer = DB::table('ec_s_customers')
        //     ->where('company_id', $user->company_id)
        //     ->where('delete_flag', 0)
        //     ->where('id', '!=', $id)
        //     ->where(function ($query) use ($postData) {
        //         $query->where('code', $postData["code"]);
        //     })
        //     ->first();
        //     $existedErr = 'UTILITES.CUSTOMER.ERR_EXISTED_CODE_EMAIL';
        // }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $customer = eCCustomers::find($id);
        if (!$customer) {
            throw new eCBusinessException('UTILITES.CUSTOMER.NOT_EXISTED_CUSTOMER');
        }

        if ($customer->company_id != $user->company_id) {
            throw new eCBusinessException('UTILITES.CUSTOMER.NOT_PERMISSION');
        }

        if ($existedCustomer) {
            throw new eCBusinessException($existedErr);
        }

        try {
            DB::beginTransaction();

            $customer->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_customers', 'UPDATE_CUSTOMER', $customer->code, json_encode($postData));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteCustomerSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_CUSTOMER", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_customers = eCCustomers::select('id','name')->whereIn('id', $ids)->where('company_id', $company_id)->get();
        $i = 1;
        foreach ($avail_customers as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->name;
        }
        $count = count($avail_rm);
        if (!isset($avail_ids)) throw new eCBusinessException('UTILITES.CUSTOMER.NOT_EXISTED_CUSTOMER');

        try {
            DB::beginTransaction();
            eCCustomers::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_customers', 'DELETE_CUSTOMER', $count, json_encode($avail_rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initDocumentTypeSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_DOCUMENT_TYPE");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $lstDocumentGroup = eCDocumentGroups::all();
        return array('permission' => $permission, 'lstDocumentGroup' => $lstDocumentGroup);
    }

    public function searchDocumentType($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_TYPE", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT dt.id, dt.dc_type_name, dt.dc_type_code, dt.note, dt.status,dt.dc_style, dt.is_order_auto, dt.dc_length, dt.is_auto_reset, dt.dc_format, dt.document_group_id, dg.name as group_name FROM s_document_types as dt JOIN ec_m_document_types as dg on dt.document_group_id = dg.id WHERE dt.company_id = ? AND dt.delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM s_document_types WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (dt.dc_type_name LIKE ? OR dt.dc_type_code LIKE ?)';
            $strCount .= ' AND (dc_type_name LIKE ? OR dc_type_code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_group_id"] != -1) {
            $str .= ' AND dt.document_group_id = ?';
            $strCount .= ' AND document_group_id = ?';
            array_push($arr, $searchData["document_group_id"]);
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND dt.status = ?';
            $strCount .= ' AND status = ?';
            array_push($arr, $searchData["status"]);
        }
        if ($searchData["dc_style"] != -1)
        {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertDocumentType($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_TYPE", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            'dc_type_code' => 'required|max:25',
            'dc_type_name' => 'required|max:255',
            'document_group_id' => 'required|max:11|exists:ec_m_document_types,id',
            'dc_style' => 'required|max:25',
            'note' => 'max:255',
            'status' => 'boolean',
            'is_order_auto' => 'boolean',
            'is_auto_reset' => 'boolean',
        ];

        if ($postData['is_order_auto'] == 1) {
            if(!$postData['is_auto_reset']){
                $rules['dc_length'] = 'required|max:4';
            }
            $rules['dc_format'] = 'required|max:50';
        } else {
            $rules['dc_length'] = 'max:4';
            $rules['dc_format'] = 'max:50';
        }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $existedDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where(function ($query) use ($postData) {
                $query->where([
                    ['dc_type_code', '=', $postData['dc_type_code']],
                    ['document_group_id', '=', $postData['document_group_id']]
                ])
                    ->orWhere('dc_type_name', $postData['dc_type_name']);
            })
            ->first();
        if ($existedDocumentType) throw new eCBusinessException('UTILITES.DOCUMENT_TYPE.EXISTED_CODE_NAME');
        $raw_log = $postData;

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCDocumentTypes::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'s_document_types', 'INSERT_DOCUMENT_TYPE', $postData['dc_type_name'], json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateDocumentType($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_TYPE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'dc_type_code' => 'required|max:25',
            'dc_type_name' => 'required|max:255',
            'document_group_id' => 'required|max:11|exists:ec_m_document_types,id',
            'dc_style' => 'required|max:25',
            'note' => 'max:255',
            'status' => 'boolean',
            'is_order_auto' => 'boolean',
            'is_auto_reset' => 'boolean',
        ];

        if ($postData['is_order_auto'] == 1) {
            if(!$postData['is_auto_reset']){
                $rules['dc_length'] = 'required|max:4';
            }
            $rules['dc_format'] = 'required|max:50';
        } else {
            $rules['dc_length'] = 'max:4';
            $rules['dc_format'] = 'max:50';
        }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $document_type = eCDocumentTypes::find($id);
        if (!$document_type) {
            throw new eCBusinessException('UTILITES.DOCUMENT_TYPE.NOT_EXISTED_DOCUMENT_TYPE');
        }

        if ($document_type->company_id != $user->company_id) {
            throw new eCBusinessException('UTILITES.DOCUMENT_TYPE.NOT_PERMISSION');
        }

        $existedDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($postData) {
                $query->where([
                    ['dc_type_code', '=', $postData['dc_type_code']],
                    ['document_group_id', '=', $postData['document_group_id']]
                ])
                    ->orWhere('dc_type_name', $postData['dc_type_name']);
            })
            ->first();

        if ($existedDocumentType) {
            throw new eCBusinessException('UTILITES.DOCUMENT_TYPE.EXISTED_DC_TYPE_CODE_DC_TYPE_NAME');
        }

        try {
            DB::beginTransaction();

            $document_type->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'s_document_types', 'UPDATE_DOCUMENT_TYPE', $document_type->dc_type_name, json_encode($postData));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteDocumentTypeSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_TYPE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_document_types = eCDocumentTypes::select('id')->whereIn('id', $ids)->where('company_id', $company_id)->get();
        foreach ($avail_document_types as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('UTILITES.DOCUMENT_TYPE.NOT_EXISTED_DOCUMENT_TYPE');
        $isUsingDocumentTypes = eCDocuments::select('ec_documents.document_type_id')
        ->selectRaw('COUNT(ec_documents.id) as nr_document_type')
        ->whereIn('ec_documents.document_type_id', $ids)
        ->having('nr_document_type', '>', 0)
        ->groupBy('ec_documents.document_type_id')
        ->get();
        $using_ids = [];
        foreach ($isUsingDocumentTypes as $d) {
            $using_ids[] = $d->document_type_id;
        }
        // All document_type are using. Need to remove document first.
        if (count($using_ids) == count($avail_ids)) {
            throw new eCBusinessException('SERVER.IN_USE_DATA');
        }
        $rm_ids = array_diff($avail_ids, $using_ids);
        $i = 1;
        $avail_per = eCDocumentTypes::select('dc_type_name')->whereIn('id', $rm_ids)->where('company_id', $company_id)->get();
        foreach ($avail_per as $per){
            $rm[$i++] = $per->dc_type_name;
        }
        $count = count($rm_ids);
        try {
            DB::beginTransaction();
            eCDocumentTypes::whereIn('id', $rm_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'s_document_types', 'DELETE_DOCUMENT_TYPE', $count, json_encode($rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initEmployeeSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_EMPLOYEE");
        $createDocPermissionInternal = $this->permissionService->getPermission($user->role_id,  "INTERNAL_CREATE");
        $createDocPermissionCommerce = $this->permissionService->getPermission($user->role_id,  "COMMERCE_CREATE");
        if($createDocPermissionInternal->is_view != 1 || $createDocPermissionInternal->is_write != 1 || $createDocPermissionCommerce->is_view != 1 || $createDocPermissionCommerce->is_write != 1){
            if (!$permission || $permission->is_view != 1 ) {
                throw new eCAuthenticationException();
            }
        }
        $lstDepartment = eCDepartments::where('company_id', $user->company_id)->where('status', 1)->get();
        $lstPosition = eCPositions::where('company_id', $user->company_id)->where('status', 1)->get();

        return array('lstPosition' => $lstPosition, 'lstDepartment' => $lstDepartment, 'permission' => $permission);
    }

    public function searchEmployee($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_EMPLOYEE", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $str = 'SELECT e.*, d.name as department_name, p.name as position_name FROM ec_s_employees e JOIN ec_s_departments d ON e.department_id = d.id JOIN ec_s_positions p ON e.position_id = p.id WHERE e.company_id = ? AND e.delete_flag = 0 ';
            $strCount = 'SELECT count(*) as cnt FROM ec_s_employees WHERE company_id = ? AND delete_flag = 0 ';
            $arr = array($user->company_id);

            if (!empty($searchData["keyword"])) {
                $str .= ' AND (e.emp_code LIKE ? OR e.emp_name LIKE ?)';
                $strCount .= ' AND (emp_code LIKE ? OR emp_name LIKE ?)';
                array_push($arr, '%' . $searchData["keyword"] . '%');
                array_push($arr, '%' . $searchData["keyword"] . '%');
            }

            if ($searchData["status"] != -1) {
                $str .= ' AND e.status = ?';
                $strCount .= ' AND status = ?';
                array_push($arr, $searchData["status"]);
            }

            if ($searchData["department_id"] != -1) {
                $str .= ' AND e.department_id = ?';
                $strCount .= ' AND department_id = ?';
                array_push($arr, $searchData["department_id"]);
            }

            if ($searchData["position_id"] != -1) {
                $str .= ' AND e.position_id = ?';
                $strCount .= ' AND position_id = ?';
                array_push($arr, $searchData["position_id"]);
            }

            $str .= " ORDER BY " . $sortQuery;

            $str .= " LIMIT ? OFFSET  ? ";
            array_push($arr, $limit, $start);

            $res = DB::select($str, $arr);
            $resCount = DB::select($strCount, $arr);

            return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertEmployee($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_EMPLOYEE", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            'emp_code' => 'nullable|max:25',
            'reference_code' => 'nullable|max:25',
            'emp_name' => 'required|max:250',
            'dob' => 'required|date',
            'sex' => 'required|in:0,1,2',
            'address1' => 'nullable|max:512',
            'address2' => 'nullable|max:512',
            'ethnic' => 'nullable|max:125',
            'nationality' => 'nullable|max:255',
            'national_id' => 'nullable|max:50',
            'national_date' => 'nullable|date',
            'national_address_provide' => 'nullable|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|max:25',
            'department_id' => 'required',
            'position_id' => 'required',
            'note' => 'nullable|max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        Log::info($postData);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $existedEmployee = eCEmployee::where('company_id', $user->company_id)
                ->where('emp_code', '!=', null)
                ->where(function ($query) use ($postData) {
                    $query->where('emp_code', $postData['emp_code'])
                        ->orWhere('email', $postData['email']);
                })
                ->first();
        if ($existedEmployee) {
            throw new eCBusinessException('UTILITES.EMPLOYEE.EXISTED_CODE_EMAIL');
        }
        $raw_log =$postData;
        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCEmployee::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_employees', 'INSERT_EMPLOYEE', $postData['emp_code'], json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateEmployee($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_EMPLOYEE", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            'emp_code' => 'nullable|max:25',
            'reference_code' => 'max:25',
            'emp_name' => 'required|max:250',
            'dob' => 'required|date',
            'sex' => 'required|in:0,1,2',
            'address1' => 'nullable|max:512',
            'address2' => 'nullable|max:512',
            'ethnic' => 'max:125',
            'nationality' => 'max:255',
            'national_id' => 'nullable|max:50',
            'national_date' => 'nullable|date',
            'national_address_provide' => 'nullable|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'max: 25',
            'department_id' => 'required',
            'position_id' => 'required',
            'note' => 'max:255',
            'status' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $employee = eCEmployee::find($id);
        if (!$employee) {
            throw new eCBusinessException('UTILITES.EMPLOYEE.NOT_EXISTED_EMPLOYEE');
        }

        if ($employee->company_id != $user->company_id) {
            throw new eCBusinessException('UTILITES.EMPLOYEE.NOT_PERMISSION');
        }

        $existedEmployee = eCEmployee::where('company_id', $user->company_id)
                ->where('id', '!=', $id)
                ->where('emp_code', '!=', null)
                ->where(function ($query) use ($postData) {
                    $query->where('emp_code', $postData['emp_code'])
                        ->orWhere('email', $postData['email']);
                })
                ->first();

        if ($existedEmployee) {
            throw new eCBusinessException('UTILITES.EMPLOYEE.EXISTED_CODE_EMAIL');
        }

        try {
            DB::beginTransaction();

            $employee->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_employees', 'UPDATE_EMPLOYEE', $employee->emp_code, json_encode($postData));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteEmployeeSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_EMPLOYEE", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;

        $avail_employeess = eCEmployee::select('id', 'emp_name')->whereIn('id', $ids)->where('company_id', $company_id)->get();
        $i = 1;
        foreach ($avail_employeess as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->emp_name;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('UTILITES.EMPLOYEE.NOT_EXISTED_EMPLOYEE');

        try {
            DB::beginTransaction();
            eCEmployee::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_s_employees', 'DELETE_EMPLOYEE', $count, json_encode($avail_ids));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function checkExistEmployee($postData){
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_EMPLOYEE", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            'email' => 'email|max:255'
        ];
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $employee = eCEmployee::where('email', $postData['email'])->first();
        if (!$employee) {
            return array('status' => 404);
        }
        return array('status' => 200, 'employee' => $employee);
    }

    public function checkExistCustomer($postData){
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_CUSTOMER", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $rules = [
            'email' => 'email|max:255'
        ];
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }
        $customer = eCEmployee::where('email', $postData['email'])->first();
        if (!$customer) {
            return array('status' => 404);
        }
        return array('status' => 200, 'customer' => $customer);
    }

    public function initBranchSetting(){
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_BRANCH");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        return array('permission' => $permission);
    }

    public function searchBranch($searchData, $draw, $start, $limit, $sortQuery){
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_BRANCH", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $str = 'SELECT id, name, address, phone, email, branch_code, tax_number, status FROM ec_branches WHERE company_id = ? AND delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_branches WHERE company_id = ? AND delete_flag = 0 ';
        $arr = array($user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (address LIKE ? OR name LIKE ?)';
            $strCount .= ' AND (address LIKE ? OR name LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
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

    public function insertBranch($postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_BRANCH", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            'name' => 'required|max:255',
            'tax_number' => 'nullable|max:30',
            'address' => 'required|max:255',
            'phone' => 'nullable|max:30',
            'email' => 'nullable|max:255',
            'status' => 'nullable|boolean',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $existedBranch = eCBranch::where('company_id', $user->company_id)
            ->where('name', $postData["name"])
            ->first();
        if ($existedBranch) {
            throw new eCBusinessException('UTILITES.BRANCH.EXISTED_NAME');
        }

        $raw_log = $postData;

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCBranch::create($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_branches', 'INSERT_BRANCH', $postData['name'], json_encode($raw_log));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateBranch($id, $postData)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_BRANCH", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $rules = [
            'name' => 'required|max:255',
            'tax_number' => 'nullable|max:30',
            'address' => 'required|max:255',
            'phone' => 'nullable|max:30',
            'email' => 'nullable|max:255',
            'status' => 'nullable|boolean',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $branch = eCBranch::find($id);
        if (!$branch) {
            throw new eCBusinessException('UTILITES.BRANCH.NOT_EXISTED_BRANCH');
        }

        if ($branch->company_id != $user->company_id) {
            throw new eCBusinessException('UTILITES.BRANCH.NOT_PERMISSION');
        }

        $existedBranch = eCBranch::where('company_id', $user->company_id)
            ->where('id', '!=', $id)
            ->where('name', $postData["name"])
            ->first();
        if ($existedBranch) {
            throw new eCBusinessException('UTILITES.BRANCH.EXISTED_NAME');
        }

        try {
            DB::beginTransaction();

            $branch->update($postData);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_branches', 'UPDATE_BRANCH', $branch->name, json_encode($postData));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteBranchSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_BRANCH", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;

        $avail_branches = eCBranch::select('id')->whereIn('id', $ids)->where('company_id', $company_id)->get();

        foreach ($avail_branches as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('UTILITES.BRANCH.NOT_EXISTED_BRANCH');

        $isUsingBranchs = eCUser::select('ec_users.branch_id')
            ->selectRaw('COUNT(ec_users.id) as nr_branch')
            ->whereIn('ec_users.branch_id', $ids)
            ->having('nr_branch', '>', 0)
            ->groupBy('ec_users.branch_id')
            ->get();
        $using_ids = [];
        foreach ($isUsingBranchs as $p) {
            $using_ids[] = $p->branch_id;
        }

        if (count($using_ids) == count($avail_ids)) {
            throw new eCBusinessException('SERVER.IN_USE_DATA');
        }

        $rm_ids = array_diff($avail_ids, $using_ids);
        $i = 1;
        $avail_per = DB::table('ec_branches')->select('name')
            ->whereIn('id', $rm_ids)->where('company_id', $company_id)
            ->where('delete_flag', 0)->get()->toArray();
        foreach($avail_per as $per){
            $rm[$i++] = $per->name;
        }
        $count = count($rm_ids);

        try {
            DB::beginTransaction();
            eCBranch::whereIn('id', $rm_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::UTILITIES_ACTION,'ec_branches', 'DELETE_BRANCH', $count, json_encode($rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function changeBranchStatus($id, $status)
    {
        $user = Auth::user();
        $branch = eCBranch::find($id);
        if (!$branch) {
            throw new eCBusinessException('UTILITES.BRANCH.NOT_EXISTED_BRANCH');
        }

        $branch->update(['status' => $status]);

        return true;
    }

}
