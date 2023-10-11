<?php

namespace Admin\Controllers;

use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Customer\Exceptions\eCBusinessException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Admin\Services\ConfigService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminConfigController extends eCBaseController
{

    private $configService;

    /**
     * AdminConfigController constructor.
     * @param $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function initUser(Request $request)
    {
        try {
            $result = $this->configService->initUserSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][initUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchUser(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->configService->searchUser($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][searchUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $name = $request->json("name");
            $email = Str::lower($request->json('email'));
            $note = $request->json("note");
            $role_id = $request->json("role_id");
            $agency_id = $request->json("agency_id");
            $password = $request->json("password");
            $status = $request->json("status");
            $language = $request->json("language");

            $postData = [
                'full_name' => base64_encode($name),
                'email' => $email,
                'role_id' => $role_id,
                'agency_id' => $agency_id,
                'password' => $password,
                'note' => $note,
                'status' => $status,
                'language' => $language,
            ];
            $rules = [
                'full_name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'note' => 'max:512',
                'role_id' => 'required|max:10|exists:ec_s_roles,id',
                'agency_id' => 'nullable|max:10|exists:ec_agencies,id',
                'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'status' => 'boolean',
                'language' => 'in:vi,en'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->insertUser($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][createNewUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $id = $request->json("id");
            $name = $request->json("name");
            $email = Str::lower($request->json('email'));
            $note = $request->json("note");
            $role_id = $request->json("role_id");
            $agency_id = $request->json("agency_id");
            $password = $request->json("password");
            $status = $request->json("status");
            $language = $request->json("language");

            $postData = [
                'full_name' => base64_encode($name),
                'email' => $email,
                'role_id' => $role_id,
                'agency_id' => $agency_id,
                'password' => $password,
                'note' => $note,
                'status' => $status,
                'language' => $language,
            ];
            $rules = [
                'full_name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'note' => 'max:512',
                'role_id' => 'required|max:10|exists:ec_s_roles,id',
                'agency_id' => 'nullable|max:10|exists:ec_agencies,id',
                'status' => 'boolean',
                'language' => 'in:vi,en'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails())  throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->updateUser($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function changePasswordUser(Request $request)
    {
        try {

            $id = $request->json("id");
            $password = $request->json("password");
            $email = $request->json("email");

            $postData = [
                'password' => $password,
                'email' => $email,
            ];

            $rules = [
                'password' => 'required|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->changePasswordUser($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteUserSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiUser(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deleteUserSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][deleteMultiUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initTemplate(Request $request)
    {
        try {
            $result = $this->configService->initTemplateSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][initTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchTemplate(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->configService->searchTemplate($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][searchTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateTemplate(Request $request)
    {
        try {
            $id = $request->json("id");
            $template_content = $request->json("template_content");

            $postData = [
                'template' => $template_content
            ];

            $res = $this->configService->updateTemplate($id, $postData);
            return $this->sendResponse($res, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteTemplate(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteTemplateSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiTemplate(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deleteTemplateSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][deleteMultiTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initAgency()
    {
        try {
            $result = $this->configService->initAgencySetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][initAgency] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchAgency(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->configService->searchAgency($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][searchAgency] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createAgency(Request $request)
    {
        try {
            $agency_name = $request->json("agency_name");
            $agency_phone = $request->json("agency_phone");
            $agency_email = $request->json("agency_email");
            $agency_address = $request->json("agency_address");
            $agency_fax = $request->json("agency_fax");
            $create_acc = $request->json("create_acc");

            $postData = [
                'agency_name' => $agency_name,
                'agency_email' => $agency_email,
                'agency_phone' => $agency_phone,
                'agency_fax' => $agency_fax,
                'agency_address' => $agency_address,
            ];
            $rules = [
                'agency_email' => 'required|email|max:255',
                'agency_phone' => 'nullable|digits_between:8,15',
                'agency_address' => 'required|max:25',
                'agency_name' => 'nullable|max:255',
                'agency_fax' => 'nullable|max:30',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->insertAgency($postData, $create_acc);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][createNewAgency] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateAgency(Request $request)
    {
        try {
            $id = $request->json("id");
            $agency_name = $request->json("agency_name");
            $agency_phone = $request->json("agency_phone");
            $agency_email = $request->json("agency_email");
            $agency_address = $request->json("agency_address");
            $agency_fax = $request->json("agency_fax");

            $postData = [
                'agency_name' => $agency_name,
                'agency_email' => $agency_email,
                'agency_phone' => $agency_phone,
                'agency_fax' => $agency_fax,
                'agency_address' => $agency_address,
            ];
            $rules = [
                'agency_email' => 'required|email|max:255',
                'agency_phone' => 'nullable|digits_between:8,15',
                'agency_address' => 'required|max:25',
                'agency_name' => 'nullable|max:255',
                'agency_fax' => 'nullable|max:30',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

            $result = $this->configService->updateAgency($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateAgency] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deleteAgency(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteAgencySetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteAgency] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiAgency(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deleteAgencySetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][deleteMultiAgency] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function changeAgencyStatus(Request $request){
        try {
            $id = $request->json("id");
            $status = $request->json("status");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->changeAgencyStatus($id, $status);
            if($status == 0) {
                return $this->sendResponse($result, 'SERVER.PAUSE_AGENCY_SUCCESSFUL');
            } else {
                return $this->sendResponse($result, 'SERVER.ACTIVE_AGENCY_SUCCESSFUL');
            }
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][changeAgencyStatus] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initServiceConfig()
    {
        try {
            $result = $this->configService->initServiceConfigSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][initServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchServiceConfig(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->configService->searchServiceConfig($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][searchServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createServiceConfig(Request $request)
    {
        try {
            $service_code = $request->json("service_code");
            $service_name = $request->json("service_name");
            $description = $request->json("description");
            $service_type = $request->json("service_type");
            $status = $request->json('status');
            $price = $request->json('price');
            $quantity =$request->json('quantity');
            $expires_time = $request->json('expires_time');
            $postData = [
                'service_code' => $service_code,
                'service_name' => $service_name,
                'service_type' => $service_type,
                'description' => $description,
                'status' => $status,
                'price' => $price,
                'quantity' =>$quantity,
                'expires_time' => $expires_time,
            ];
            $rules = [
                'service_code' => 'required|alpha_dash|max:50',
                'service_name' => 'required|max:255',
                'service_type' => 'required|in:1,2',
                'description' => 'nullable|max:255',
                'status' => 'boolean',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->insertServiceConfig($postData);
            Log::info($result);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][createNewServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateServiceConfig(Request $request)
    {
        try {
            $id = $request->json("id");
            $service_code = $request->json("service_code");
            $service_name = $request->json("service_name");
            $description = $request->json("description");
            $service_type = $request->json("service_type");
            $status = $request->json('status');
            $price = $request->json('price');
            $quantity =$request->json('quantity');
            $expires_time =$request->json('expires_time');
            $postData = [
                'service_code' => $service_code,
                'service_name' => $service_name,
                'service_type' => $service_type,
                'description' => $description,
                'status' => $status,
                'price' =>$price,
                'quantity' => $quantity,
                'expires_time' => $expires_time,
            ];
            $rules = [
                'service_code' => 'required|alpha_dash|max:50',
                'service_name' => 'required|max:255',
                'service_type' => 'required|in:1,2',
                'description' => 'nullable|max:255',
                'status' => 'boolean',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->updateServiceConfig($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deleteServiceConfig(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteServiceConfigSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiServiceConfig(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deleteServiceConfigSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][deleteMultiServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function changeServiceConfigStatus(Request $request){
        try {
            $id = $request->json("id");
            $status = $request->json("status");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->changeServiceConfigStatus($id, $status);
            if($status == 0) {
                return $this->sendResponse($result, 'SERVER.PAUSE_SERVICE_CONFIG_SUCCESSFUL');
            } else {
                return $this->sendResponse($result, 'SERVER.ACTIVE_SERVICE_CONFIG_SUCCESSFUL');
            }
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][changeServiceConfigStatus] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getServiceConfigDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->getServiceConfigDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][getServiceConfigDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function saveServiceConfigDetail(Request $request){
        $service_config_id = $request->json('service_config_id');
        $from = $request->json('from');
        $to = $request->json('to');
        $fee = $request->json('fee');
        try {
            $postData = [
                'service_config_id' => $service_config_id,
                'from' => $from,
                'to' => $to,
                'fee' => $fee
            ];
            $rules = [
                'service_config_id' => 'required|exists:s_service_config,id',
                'from' => 'required|integer|min:1',
                'to' => 'required|integer',
                'fee' => 'required|alpha_num',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->saveServiceConfigDetail($postData);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteServiceConfigDetail(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteServiceConfigDetail([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteServiceConfig] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function initDocumentTutorial()
    {
        try {
            $result = $this->configService->initTutorialSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][initDocumentTutorial] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchDocumentTutorial(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");
            $result = $this->configService->searchDocumentTutorial($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][searchDocumentTutorial] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewDocumentTutorial(Request $request)
    {
        try {
            $name = $request->json("name");
            $description = $request->json("description");
            $files = $request->json('files');
            $ip = $request->ip();

            $postData = [
                'name' => $name,
                'description' => $description,
            ];
            $rules = [
                'name' => 'required|max:255',
                'description' => 'required|max:1024'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->insertDocumentTutorial($postData, $files, $ip);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eConfigController][createNewDocumentTutorial] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateDocumentTutorial(Request $request)
    {
        try {
            $id = $request->json("id");
            $name = $request->json("name");
            $description = $request->json("description");
            $files = $request->json('files');

            $postData = [
                'name' => $name,
                'description' => $description,
            ];
            $rules = [
                'name' => 'required|max:255',
                'description' => 'nullable|max:1024'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails())  throw new eCBusinessException('SERVER.INVALID_INPUT');

            $result = $this->configService->updateTutorialDocument($id, $postData, $files);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateDocumentTutorial] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deleteDocumentTutorial(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteDocumentTutorialSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteDocumentTutorial] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiDocumentTutorial(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deleteDocumentTutorialSetting([$ids]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][deleteMultiDocumentTutorial] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function uploadDocumentFiles(Request $request) {
        try {
            $result = $this->configService->uploadFiles($request);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][uploadDocumentFiles] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function removeDocumentFile(Request $request) {
        try {
            $file_id = $request->json("file_id");
            $result = $this->configService->removeFile($file_id);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][removeDocumentFiles] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDocumentDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->getDocumentDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][getDocumentDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getTutorialDocument(Request $request) {
        try {
            $id = $request->id;
            $result = $this->configService->getTutorialDocument($id);
            return $result;
        } catch (Exception $e) {
            Log::error("[AdminServiceController][getTutorialDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initGuideVideo()
    {
        try {
            $result = $this->configService->initGuideVideoSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][initGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            $result = $this->configService->searchGuideVideo($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][searchGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewGuideVideo(Request $request)
    {
        try {
            $name = $request->json("name");
            $description = $request->json("description");
            $link = $request->json("link");
            $link = str_replace("view","preview",$link);

            $postData = [
                'name' => $name,
                'description' => $description,
                'link' => $link,
            ];
            $rules = [
                'name' => 'required|max:255',
                'description' => 'required|max:1024',
                'link' => 'required|max:1024'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->insertGuideVideo($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eConfigController][createNewGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateGuideVideo(Request $request)
    {
        try {
            $id = $request->json("id");
            $name = $request->json("name");
            $description = $request->json("description");
            $link = $request->json("link");
            $link = str_replace("view","preview",$link);

            $postData = [
                'name' => $name,
                'description' => $description,
                'link' => $link,
            ];
            $rules = [
                'name' => 'required|max:255',
                'description' => 'required|max:1024',
                'link' => 'required|max:1024'
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->configService->updateGuideVideo($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][updateGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deleteGuideVideo(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteGuideVideoSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][deleteGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiGuideVideo(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deleteGuideVideoSetting([$ids]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[AdminConfigController][deleteMultiGuideVideo] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getGuideVideoDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->getGuideVideoDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[AdminConfigController][getGuideVideoDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
