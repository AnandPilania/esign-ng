<?php


namespace Admin\Services;


use Admin\Services\Shared\NotificationService;
use Carbon\Carbon;
use Core\Helpers\Common;
use Core\Helpers\NotificationType;
use Core\Helpers\StorageHelper;
use Core\Models\eCAmsRole;
use Core\Models\eCConversationTemplates;
use Core\Models\eCDocumentTutorialResources;
use Core\Models\eCService;
use Core\Models\eCServiceConfig;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Core\Models\eCAdmin;
use Core\Models\eCDocumentTutorial;
use Core\Models\eCAgencies;
use Core\Models\eCGuideVideo;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Core\Services\ActionHistoryService;

class ConfigService
{
    private $notificationService;
    private $actionHistoryService;
    private $storageHelper;

    /**
     * eCUtilitiesService constructor.
     * @param NotificationService $notificationService
     * @param ActionHistoryService $actionHistoryService
     */
    public function __construct(NotificationService $notificationService, ActionHistoryService $actionHistoryService, StorageHelper $storageHelper)
    {
        $this->notificationService = $notificationService;
        $this->actionHistoryService = $actionHistoryService;
        $this->storageHelper = $storageHelper;

    }

    public function initUserSetting()
    {
        $user = Auth::user();
        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }
        $lstRole = eCAmsRole::all();
        $lstAgency = eCAgencies::all();
        return array('lstRole' => $lstRole, 'lstAgency' => $lstAgency);
    }

    public function searchUser($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        // if ($user->role_id != 1) {
        //     throw new eCAuthenticationException();
        // }

        $str = 'SELECT eu.id, FROM_BASE64(eu.full_name) as name, FROM_BASE64(eu.email) as email, eu.password, eu.role_id, er.role_name, eu.agency_id, eu.status, eu.language FROM ec_admins as eu JOIN ec_ams_roles as er on eu.role_id = er.id WHERE eu.delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_admins WHERE delete_flag = 0 ';
        $arr = array();

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (FROM_BASE64(eu.full_name) LIKE ? OR FROM_BASE64(eu.email) LIKE ?)';
            $strCount .= ' AND (FROM_BASE64(full_name) LIKE ? OR FROM_BASE64(email) LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["role_id"] != -1) {
            $str .= ' AND eu.role_id = ?';
            $strCount .= ' AND role_id = ?';
            array_push($arr, $searchData["role_id"]);
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

        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }

        $existedUser = eCAdmin::where('email', base64_encode($postData['email']))->first();
        if ($existedUser) {
            throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');
        }

        $postData["is_first_login"] = 0;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;
        $postData['password'] = Hash::make($postData['password']);
        $postData['email'] = $postData['email'];

        try {
            DB::beginTransaction();

            eCAdmin::create($postData);

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

        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }
        $editUser = eCAdmin::find($id);

        if (!$editUser) {
            throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_USER');
        }

        $existedUser = eCAdmin::where('id', '!=', $id)
            ->where('email', base64_encode($postData['email']))
            ->first();
        if ($existedUser) {
            throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');
        }

        $postData["updated_by"] = $user->id;
        $postData['email'] = $postData['email'];

        try {
            DB::beginTransaction();

            $editUser->update($postData);

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

        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }

        $editUser = eCAdmin::find($id);
        if (!$editUser) {
            throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_USER');
        }

        $existedUser = eCAdminwhere('id', '!=', $id)
            ->where('email', base64_encode($postData['email']))
            ->first();

        if ($existedUser) {
            throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');
        }

        $postData['password'] = Hash::make($postData['password']);
        $postData['email'] = $postData['email'];

        try {
            DB::beginTransaction();

            $editUser->update($postData);

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
        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }

        $avail_users = eCAdmin::select('id')
            ->whereIn('id', $ids)->get();
        foreach ($avail_users as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.USER.NOT_EXISTED_USER');

        try {
            DB::beginTransaction();
            eCAdmin::whereIn('id', $avail_ids)
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

    public function initTemplateSetting()
    {
        $user = Auth::user();
        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }
        $lstTemplate = eCConversationTemplates::where('status', 1)->get();
        return array('lstTemplate' => $lstTemplate);
    }

    public function searchTemplate($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        // if ($user->role_id != 1) {
        //     throw new eCAuthenticationException();
        // }
        $arr = array();
        $str = 'SELECT ct.id, ct.type, ct.template_description, ct.template AS system_template, ct.status FROM ec_s_conversation_templates ct WHERE ct.`status` = 1 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_conversation_templates ct WHERE 1=1 ';
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
        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }
        DB::beginTransaction();
        try {
            eCConversationTemplates::find($id)->update($postData);
            DB::commit();
            return array("status" => true);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }


    }

    public function initAgencySetting()
    {
        $user = Auth::user();
        if ($user->role_id != 1) {
            throw new eCAuthenticationException();
        }
        return array();
        // $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_AGENCY");
        // if (!$permission || $permission->is_view != 1) {
        //     throw new eCAuthenticationException();
        // }
        // return array('permission' => $permission);
    }

    public function searchAgency($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();

        $str = 'SELECT id, FROM_BASE64(agency_name) as agency_name, FROM_BASE64(agency_phone) as agency_phone, FROM_BASE64(agency_email) as agency_email, agency_address, status FROM ec_agencies WHERE delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_agencies WHERE delete_flag = 0 ';
        $arr = array();

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (FROM_BASE64(agency_name) LIKE ? OR FROM_BASE64(agency_phone) LIKE ? OR FROM_BASE64(agency_email) LIKE ?)';
            $strCount .= ' AND (FROM_BASE64(agency_name) LIKE ? OR FROM_BASE64(agency_phone) LIKE ? OR FROM_BASE64(agency_email) LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
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

    public function insertAgency($postData, $isCreateAcc)
    {
        $user = Auth::user();

        $email = $postData["agency_email"];

        $existedAgency = eCAgencies::where('agency_email', base64_encode($postData["agency_email"]))->first();
        if ($existedAgency) {
            throw new eCBusinessException('CONFIG.AGENCY.EXISTED_CODE_EMAIL');
        }

        $existedUser = eCAdmin::where('email', base64_encode($postData['agency_email']))->first();
        if ($existedUser) {
            throw new eCBusinessException('CONFIG.USER.EXISTED_EMAIL');
        }

        $postData['agency_email'] = $postData['agency_email'];
        $postData['agency_name'] = $postData['agency_name'];
        $postData['agency_phone'] = $postData['agency_phone'];
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            $agency = eCAgencies::create($postData);
            $agency->vendor()->create([
                "user_name" => "API_Agent_". $agency->id,
//                "password" =>
            ]);

            if ($isCreateAcc) {
                //TODO: send mail
                $password = Common::randomString(10);
                $agency->admin()->create([
                    "email" => $postData['agency_email'],
                    "password" => Hash::make($password),
                    "full_name" => $postData['agency_name'],
                    "role_id" => 2,
                ]);

                $exts = array(
                    "ten_dang_nhap" => $email,
                    "mat_khau" => $password
                );

                $this->notificationService->sendNotificationApiAccount($email, "", NotificationType::CREATE_COMPANY, $exts, 1);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateAgency($id, $postData)
    {
        $user = Auth::user();

        $agency = eCAgencies::find($id);
        if (!$agency) {
            throw new eCBusinessException('CONFIG.AGENCY.NOT_EXISTED_AGENCY');
        }

        $existedAgency = eCAgencies::where('id', '!=', $id)
            ->where('agency_email', base64_encode($postData["agency_email"]))
            ->first();
        if ($existedAgency) {
            throw new eCBusinessException('CONFIG.AGENCY.EXISTED_CODE_EMAIL');
        }

        $postData['agency_email'] = $postData['agency_email'];
        $postData['agency_name'] = $postData['agency_name'];
        $postData['agency_phone'] = $postData['agency_phone'];

        try {
            DB::beginTransaction();

            $agency->update($postData);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteAgencySetting($ids = [])
    {
        $user = Auth::user();
        $avail_agencies = eCAgencies::select('id')->whereIn('id', $ids)->get();
        foreach ($avail_agencies as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.AGENCY.NOT_EXISTED_AGENCY');

        try {
            DB::beginTransaction();
            eCAgencies::whereIn('id', $avail_ids)
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

    public function changeAgencyStatus($id, $status)
    {
        $user = Auth::user();
        $agency = eCAgencies::find($id);
        if (!$agency) {
            throw new eCBusinessException('CONFIG.AGENCY.NOT_EXISTED_AGENCY');
        }

        DB::beginTransaction();
        try {
            $agency->update(['status' => $status]);
            $updateUser = eCAdmin::where('agency_id', $id)->update(['status' => $status]);
            DB::commit();
            return true;
        } catch(Exception $e){
            DB::rollback();
            return false;
        }
    }

    public function initServiceConfigSetting()
    {
        $user = Auth::user();
//        if ($user->role_id != 1) {
//            throw new eCAuthenticationException();
//        }
        return array();
        // $permission = $this->permissionService->getPermission($user->role_id, "CONFIG_AGENCY");
        // if (!$permission || $permission->is_view != 1) {
        //     throw new eCAuthenticationException();
        // }
        // return array('permission' => $permission);
    }

    public function searchServiceConfig($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();

        $str = 'SELECT id, service_name, service_code, description, service_type, status, created_at, updated_at, price, quantity, expires_time FROM s_service_config WHERE delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM s_service_config WHERE delete_flag = 0 ';
        $arr = array();

        if (!empty($searchData["keyword"])) {
            $str .= ' AND service_name LIKE ? OR service_code LIKE ?)';
            $strCount .= ' AND service_name LIKE ? OR service_code LIKE ?)';
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

    public function insertServiceConfig($postData)
    {
        $user = Auth::user();
        $existedServiceConfig = eCService::where('service_code', '!=', null)
            ->where(function ($query) use ($postData) {
                $query->where('service_code', $postData["service_code"])
                    ->orWhere('service_name', $postData["service_name"]);
            })
            ->first();
        if ($existedServiceConfig) {
            throw new eCBusinessException('CONFIG.SERVICE_CONFIG.EXISTED_CODE_NAME');
        }

        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        try {
            DB::beginTransaction();

            eCService::create($postData);

            DB::commit();
            
            return true;
        } catch(Exception $e){
            DB::rollback();
            return $e;
        }
    }

    public function updateServiceConfig($id, $postData)
    {
        $user = Auth::user();
        if ($user->role_id != 1)  throw new eCAuthenticationException();

        $service = eCService::find($id);
        $expired_date = Carbon::now();
        $expired_date->addDays(10);
        $expired_date= Carbon::parse( $expired_date);
        Log::info((Carbon::now()->diff($expired_date))->days);
        if (!$service) {
            throw new eCBusinessException('CONFIG.SERVICE_CONFIG.NOT_EXISTED_SERVICE_CONFIG');
        }

        $existedServiceConfig = eCService::where('id', '!=', $id)
            ->where('service_code', '!=', null)
            ->where(function ($query) use ($postData) {
                $query->where('service_code', $postData["service_code"])
                    ->orWhere('service_name', $postData["service_name"]);
            })
            ->first();
        if ($existedServiceConfig) {
            throw new eCBusinessException('CONFIG.SERVICE_CONFIG.EXISTED_CODE_NAME');
        }

        try {
            DB::beginTransaction();

            $service->update($postData);

            DB::commit();
            return true;
        } catch(Exception $e){
            DB::rollback();
            return false;
        }
    }

    public function deleteServiceConfigSetting($ids = [])
    {
        $user = Auth::user();
        if ($user->role_id != 1)  throw new eCAuthenticationException();
        $avail_service_configs = eCService::select('id')->whereIn('id', $ids)->get();
        foreach ($avail_service_configs as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.SERVICE_CONFIG.NOT_EXISTED_SERVICE_CONFIG');

        try {
            DB::beginTransaction();
            eCService::whereIn('id', $avail_ids)
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

    public function changeServiceConfigStatus($id, $status)
    {
        $user = Auth::user();
        if ($user->role_id != 1)  throw new eCAuthenticationException();
        $service = eCService::find($id);
        if (!$service) {
            throw new eCBusinessException('CONFIG.SERVICE_CONFIG.NOT_EXISTED_SERVICE_CONFIG');
        }

        try {
            DB::beginTransaction();
            $service->update(['status' => $status]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getServiceConfigDetail($id)
    {
        $user = Auth::user();
        $service = eCService::find($id);
        if (!$service) {
            throw new eCBusinessException('CONFIG.SERVICE_CONFIG.NOT_EXISTED_SERVICE_CONFIG');
        }

        $lstDetail = $service->detail()->get();

        return array("lstDetail" => $lstDetail);
    }

    public function saveServiceConfigDetail($postData){
        $user = Auth::user();
        if ($user->role_id != 1)  throw new eCAuthenticationException();

        if($postData['to'] != -1 && $postData['to'] <= $postData['from']){
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }
        // validate config service
        $pre_services = eCServiceConfig::where('service_config_id', $postData['service_config_id'])->orderBy('created_at', 'desc')->first();
//        if ($pre_services && $postData['from'] != -1 && $postData['from'] < $pre_services->to )  throw new eCBusinessException('CONFIG.SERVICE_CONFIG.ERR_SERVICE_CONFIG_FROM');
//        if ($pre_services && $postData['from'] != -1 && (int)$pre_services->to - (int)$postData['from'] != 1 )  throw new eCBusinessException('CONFIG.SERVICE_CONFIG.ERR_SERVICE_CONFIG_CONTINUE');

        try {
            DB::beginTransaction();

            $service = eCServiceConfig::create($postData);

            DB::commit();
            return array('id' => $service->id, 'updated_at' => $service->updated_at);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteServiceConfigDetail($ids = [])
    {
        $user = Auth::user();
        if ($user->role_id != 1)  throw new eCAuthenticationException();
        $avail_service_config_details = eCServiceConfig::select('id')->whereIn('id', $ids)->get();
        foreach ($avail_service_config_details as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('CONFIG.SERVICE_CONFIG.NOT_EXISTED_SERVICE_CONFIG_DETAIL');

        try {
            DB::beginTransaction();
            eCServiceConfig::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                ]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function initTutorialSetting()
    {
        $user = Auth::user();
        $lstTutorial = eCDocumentTutorial::all();
        return array('lstTutorial' => $lstTutorial);
    }

    public function searchDocumentTutorial($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $arr = array();
        $str = 'SELECT distinct(et.id),et.name,et.description FROM ec_tutorial_documents et WHERE et.delete_flag = 0';
        $strCount = 'SELECT count(*) as cnt FROM ec_tutorial_documents AS et WHERE et.delete_flag = 0';
        if (!empty($searchData["keyword"])) {
            $str .= ' AND et.name LIKE ? OR et.description LIKE ?';
            $strCount .= ' AND et.name LIKE ? OR et.description LIKE ? ';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        foreach ($res as $r) {
            $r->files = [];
            $r->files = DB::select("SELECT d.* FROM ec_tutorial_document_resources as d JOIN ec_tutorial_documents as et ON d.document_tutorial_id = et.id WHERE et.id = ? ",array($r->id) );
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertDocumentTutorial($postData, $files, $ip)
    {
        $user = Auth::user();

        if(gettype($files) != gettype(array()) || count($files) <= 0){
            throw new eCBusinessException('DOCUMENT_SAMPLE.NOT_UPLOAD_FILE');
        }

        $exists_name = eCDocumentTutorial::where('name', $postData['name'])->get();
        if(count($exists_name) > 0){
            throw new eCBusinessException('DOCUMENT_TUTORIAL.EXIST_NAME');
        }

        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        DB::beginTransaction();
        try {
            $tutorial = eCDocumentTutorial::create($postData);
            $dataFiles = array();
            foreach($files as $file){
                $fileData = array();
                $fileData["file_name_raw"] = $file["name"];
                $fileData["file_type_raw"] = $file["extension"];
                $fileData["file_size_raw"] = $file["size_raw"];
                $fileData["file_path_raw"] = $file["path"];
                $fileData["file_id"] = $file["file_id"];
                $fileData["created_by"] = $user->id;
                $fileData["updated_by"] = $user->id;

                array_push($dataFiles , $fileData);
            }
            $tutorial->resource()->createMany($dataFiles);
            DB::commit();
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }
        return true;
    }

    public function updateTutorialDocument($id, $postData, $files)
    {
        $user = Auth::user();

        $document_tutorial = eCDocumentTutorial::find($id);
        if (!$document_tutorial) {
            throw new eCBusinessException('SERVER.NOT_EXISTED_DOCUMENT');
        }

        if(gettype($files) != gettype(array()) || count($files) <= 0){
            throw new eCBusinessException('DOCUMENT_SAMPLE.NOT_UPLOAD_FILE');
        }

        DB::beginTransaction();
        try {
            $document_tutorial->update($postData);
            // remove old files
            $document_tutorial->resource()->delete();
            $dataFiles = array();
            foreach($files as $file){
                $fileData = array();
                $fileData["file_name_raw"] = $file["name"];
                $fileData["file_type_raw"] = $file["extension"];
                $fileData["file_size_raw"] = $file["size_raw"];
                $fileData["file_path_raw"] = $file["path"];
                $fileData["file_id"] = $file["file_id"];
                $fileData["created_by"] = $user->id;
                $fileData["updated_by"] = $user->id;

                array_push($dataFiles , $fileData);
            }
            $tutorial->resource()->createMany($dataFiles);
            DB::commit();
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }

        return true;
    }

    public function deleteDocumentTutorialSetting($ids = [])
    {
        $user = Auth::user();

        $avail_tutorials = eCDocumentTutorial::select('id')
            ->whereIn('id', $ids)->get();
        foreach ($avail_tutorials as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_TUTORIAL.NOT_EXISTED');
        DB::beginTransaction();
        try {
            eCDocumentTutorial::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id
                ]);
            eCDocumentTutorialResources::whereIn('document_tutorial_id', $avail_ids)->delete();
                //$this->actionHistoryService->SetActivity(HistoryActionGroup::DOCUMENT_ACTION,'ec_s_document_sample', 'DELETE_DOCUMENT_SAMPLE', 'Xóa tài liệu mẫu', json_encode($avail_ids));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }


    public function uploadFiles($request)
    {
        $user = Auth::user();
        if (!$request->hasFile('files')) {
            throw new eCBusinessException('SERVER.INVALID_INPUT');
        }

        $files = $request->file("files");
        foreach ($files as $file) {
            if($file->extension() != 'pdf'){
                throw new eCBusinessException("DOCUMENT_SAMPLE.INVALID_FILE_TYPE");
            }
            if($file->getSize() > 5242880){
                throw new eCBusinessException("DOCUMENT_SAMPLE.ERR_OVERSIZE_UPLOAD");
            }
        }
        try {
            $lstFileUploaded = array();
            foreach ($files as $file) {
                //TODO: Call API to convert and merge pdf
                $fileUploaded = array();
                $path = $this->storageHelper->uploadFile($file, '/tutorial/', false);
                $file_id = explode(".",explode("/", $path)[2])[0];
                $fileUploaded["file_id"] = $file_id;
                $fileUploaded["name"] = $file->getClientOriginalName();
                $fileUploaded["extension"] = $file->extension();
                $fileUploaded["size"] = $file->getSize();
                $fileUploaded["path"] = $path;
                array_push($lstFileUploaded, $fileUploaded);
            }
            return array('lstFileUploaded' => $lstFileUploaded);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function removeFile($file_id){
        try {
            if($file_id != null || $file_id != ""){
                eCDocumentTutorialResources::where('file_id', $file_id)->delete();
            } else {
                throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDocumentDetail($id)
    {
        $user = Auth::user();
        $service = eCDocumentTutorial::find($id);
        if (!$service) {
            throw new eCBusinessException('SERVER.NOT_EXISTED_DOCUMENT');
        }

        $lstDetail = $service->resource()->all();

        return array("tutorial" => $service, "lstDetail" => $lstDetail);
    }

    public function getTutorialDocument($id)
    {
        $user = Auth::user();
        $doc = DB::table('ec_tutorial_documents as e')
        ->join('ec_tutorial_document_resources as r','e.id', '=' ,'r.document_tutorial_id')
        ->select('e.*','r.file_path_raw')
        ->where('e.id', $id)
        ->first();
        if (!$doc) {
            throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        }
        return $this->storageHelper->downloadFile($doc->file_path_raw);
    }

    public function initGuideVideoSetting()
    {
        $user = Auth::user();
        $lstGuideVideo = eCGuideVideo::all();
        return array('lstGuideVideo' => $lstGuideVideo);
    }

    public function searchGuideVideo($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $arr = array();
        $str = 'SELECT distinct(et.id),et.name,et.description,et.link FROM ec_guide_video et WHERE et.delete_flag = 0';
        $strCount = 'SELECT count(*) as cnt FROM ec_guide_video AS et WHERE et.delete_flag = 0';
        if (!empty($searchData["keyword"])) {
            $str .= ' AND et.name LIKE ? OR et.description LIKE ?';
            $strCount .= ' AND et.name LIKE ? OR et.description LIKE ? ';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function insertGuideVideo($postData)
    {
        $user = Auth::user();

        $exists_name = eCGuideVideo::where('name', $postData['name'])->get();
        if(count($exists_name) > 0){
            throw new eCBusinessException('GUIDE_VIDEO.EXIST_NAME');
        }

        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        DB::beginTransaction();
        try {
            $insertGuideVideo = eCGuideVideo::create($postData);
            DB::commit();
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }
        return true;
    }

    public function updateGuideVideo($id, $postData)
    {
        $user = Auth::user();
        $guideVideo = eCGuideVideo::find($id);
        if (!$guideVideo) {
            throw new eCBusinessException('SERVER.NOT_EXISTED_VIDEO');
        }
        try {
            DB::beginTransaction();
            $guideVideo->update($postData);
            DB::commit();
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }

        return true;
    }

    public function deleteGuideVideoSetting($ids = [])
    {
        $user = Auth::user();

        $avail_videos = eCGuideVideo::select('id')->whereIn('id', $ids)->get();
        foreach ($avail_videos as $p) {
            $avail_ids[] = $p->id;
        }
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_TUTORIAL.NOT_EXISTED');
        DB::beginTransaction();
        try {
            eCGuideVideo::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id
                ]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getGuideVideoDetail($id)
    {
        $user = Auth::user();
        $service = eCGuideVideo::find($id);
        if (!$service) {
            throw new eCBusinessException('SERVER.NOT_EXISTED_VIDEO');
        }

        return array("guideVideo" => $service);
    }

}
