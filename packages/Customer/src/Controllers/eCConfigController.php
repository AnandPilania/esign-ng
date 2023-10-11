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
use Customer\Services\eCConfigService;

class eCConfigController extends eCBaseController
{

    private $configService;

    /**
     * eCConfigController constructor.
     * @param $configService
     */
    public function __construct(eCConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function initCompany(Request $request)
    {
        try {
            $result = $this->configService->initCompanySetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][initCompany] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateCompany(Request $request)
    {
        try {

            $id = $request->json('id');
            $name = $request->json("name");
            $email = $request->json("email");
            $address = $request->json("address");
            $phone = $request->json("phone");
            $tax_number = $request->json("tax_number");
            $fax_number = $request->json("fax_number");
            $sign_type = $request->json("sign_type");
            $website = $request->json("website");
            $representative = $request->json("representative");
            $representative_position = $request->json("representative_position");
            $bank_info = $request->json("bank_info");
            $bank_number = $request->json("bank_number");
            $contact_name = $request->json("contact_name");
            $contact_phone = $request->json("contact_phone");
            $contact_email = $request->json("contact_email");
            $status = $request->json('status');

            $postData = [
                'name' => $name,
                'email' => $email,
                'address' => $address,
                'phone' => $phone,
                'representative' => $representative,
                'representative_position' => $representative_position,
                'website' => $website,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'contact_email' => $contact_email,
                'tax_number' => $tax_number,
                'fax_number' => $fax_number,
                'bank_info' => $bank_info,
                'bank_number' => $bank_number,
                'sign_type' => $sign_type,
                'status' => $status,
            ];
            $result = $this->configService->updateCompany($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateCompany] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchCompanyConsignee(Request $request){
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->configService->searchCompanyConsignee($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][searchChucVu] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createCompanyConsignee(Request $request)
    {
        try {

            $name = $request->json("name");
            $email = $request->json("email");
            $phone = $request->json("phone");
            $role = $request->json("role");
            $status = $request->json("status");

            $postData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'status' => $status,

            ];

            $result = $this->configService->insertCompanyConsignee($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][createNewCompanyConsignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateCompanyConsignee(Request $request)
    {
        try {

            $id = $request->json('id');
            $name = $request->json("name");
            $email = $request->json("email");
            $phone = $request->json("phone");
            $role = $request->json("role");
            $status = $request->json("status");

            $postData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'status' => $status,
            ];

            $result = $this->configService->updateCompanyConsignee($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateCompanyConsignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteCompanyConsignee(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deleteCompanyConsigneeSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCConfigController][deleteCompanyConsignee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateCompanyRemoteSign(Request $request)
    {
        try {

            $id = $request->json('id');
            $provider = $request->json("provider");
            $service_signing = $request->json("service_signing");
            $login = $request->json("login");
            $password = $request->json("password");
            $status = $request->json("status");

            $postData = [
                'provider' => $provider,
                'service_signing' => $service_signing,
                'login' => $login,
                'status' => $status,
                'password' => $password, // ma hoa sau

            ];

            $result = $this->configService->updateCompanyRemoteSign($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateCompanyRemoteSign] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateCompanySignature(Request $request)
    {
        try {

            $id = $request->json('id');
            $image_signature = $request->json("image_signature");
            $postData = [
                'image_signature' => $image_signature,
            ];

            $result = $this->configService->updateCompanySignature($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateCompanySignature] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initSendDocument()
    {
        try {
            $result = $this->configService->initSendDocumentSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][initSendDocument] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateSendTime(Request $request)
    {
        try {
            $document_expire_day = $request->json("document_expire_day");
            $near_expired_date = $request->json("near_expired_date");
            $near_doc_expired_date = $request->json("near_doc_expired_date");

            $postData = [
//                'send_email_remind_day' => $send_email_remind_day,
                'document_expire_day' => $document_expire_day,
                'near_expired_date' => $near_expired_date,
                'near_doc_expired_date' => $near_doc_expired_date,
            ];

            $result = $this->configService->updateSendTime($postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateSendTime] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateEmail(Request $request)
    {
        try {
            $id = $request->json('id');
            $email_host = $request->json("email_host");
            $email_protocol = $request->json("email_protocol");
            $email_address = $request->json("email_address");
            $email_password = $request->json("email_password");
            $email_name = $request->json("email_name");
            $port = $request->json("port");
            $is_use_ssl = $request->json("is_use_ssl");
            $is_relay = $request->json("is_relay");
            $status = $request->json("status");
            $postData = [
                'id' => $id,
                'email_host' => $email_host,
                'email_protocol' => $email_protocol,
                'email_address' => $email_address,
                'email_password' => $email_password,
                'email_name' => $email_name,
                'port' => $port,
                'is_use_ssl' => $is_use_ssl,
                'is_relay' => $is_relay,
                'status' => $status,
            ];

            $result = $this->configService->updateEmail($postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateEmail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateSms(Request $request)
    {
        try {
            $id = $request->json('id');
            $service_provider = $request->json("service_provider");
            $service_url = $request->json("service_url");
            $brandname = $request->json("brandname");
            $sms_account = $request->json("sms_account");
            $sms_password = $request->json("sms_password");
            $status = $request->json("status");

            $postData = [
                'id' => $id,
                'service_provider' => $service_provider,
                'service_url' => $service_url,
                'brandname' => $brandname,
                'sms_account' => $sms_account,
                'sms_password' => $sms_password,
                'status' => $status
            ];

            $result = $this->configService->updateSms($postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateSms] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initPermission()
    {
        try {
            $result = $this->configService->initPermissionSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][initPermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initDetailPermission(Request $request)
    {
        try {
            $user = Auth::user();
            $id = $request->json("role");

            $result = $this->configService->initDetailPermissionSetting($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][initDetailPermissionSetting] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchPermission(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->configService->searchPermission($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][searchPermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewPermission(Request $request)
    {
        try {
            $role_name = $request->json("role_name");
            $note = $request->json("note");
            $status = $request->json("status");
            $lstPermission = $request->json("lstPermission");

            $postData = [
                'role_name' => $role_name,
                'status' => $status,
                'note' => $note,
            ];

            $result = $this->configService->insertPermission($postData, $lstPermission);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][createNewPermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updatePermission(Request $request)
    {
        try {

            $id = $request->json('id');
            $role_name = $request->json("role_name");
            $note = $request->json("note");
            $status = $request->json("status");
            $lstPermission = $request->json("lstPermission");

            $postData = [
                'role_name' => $role_name,
                'status' => $status,
                'note' => $note,
            ];

            $result = $this->configService->updatePermission($id, $postData, $lstPermission);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updatePermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deletePermission(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->configService->deletePermissionSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCConfigController][deletePermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiPermission(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->configService->deletePermissionSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][deleteMultiPermission] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initUser(Request $request)
    {
        try {
            $result = $this->configService->initUserSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][initUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            Log::error("[eCConfigController][searchUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $company_id = $request->json("company_id");
            $name = $request->json("name");
            $email = $request->json("email");
            $note = $request->json("note");
            $role_id = $request->json("role_id");
            $branch_id = $request->json("branch_id");
            $password = $request->json("password");
            $is_personal = $request->json("is_personal");
            $status = $request->json("status");
            $language = $request->json("language");

            $postData = [
                'company_id' => $company_id,
                'name' => $name,
                'email' => $email,
                'role_id' => $role_id,
                'branch_id' => $branch_id,
                'password' => $password,
                'note' => $note,
                'status' => $status,
                'is_personal' => $is_personal,
                'language' => $language,
            ];

            $result = $this->configService->insertUser($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][createNewUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $id = $request->json("id");
            $company_id = $request->json("company_id");
            $name = $request->json("name");
            $email = $request->json("email");
            $note = $request->json("note");
            $role_id = $request->json("role_id");
            $branch_id = $request->json("branch_id");
            // $password = $request->json("password");
            $is_personal = $request->json("is_personal");
            $status = $request->json("status");
            $language = $request->json("language");

            $postData = [
                'company_id' => $company_id,
                'name' => $name,
                'email' => $email,
                'role_id' => $role_id,
                'branch_id' => $branch_id,
                // 'password' => $password,
                'note' => $note,
                'status' => $status,
                'is_personal' => $is_personal,
                'language' => $language,
            ];

            $result = $this->configService->updateUser($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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

            $result = $this->configService->changePasswordUser($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            Log::error("[eCConfigController][deleteUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            Log::error("[eCConfigController][deleteMultiUser] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initTemplate(Request $request)
    {
        try {
            $result = $this->configService->initTemplateSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCConfigController][initTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            Log::error("[eCConfigController][searchTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateTemplate(Request $request)
    {
        try {
            $system_template_id = $request->json("id");
            $company_template_id = $request->json("company_template_id");
            $template_content = $request->json("template_content");
            $ct_status = $request->json("ct_status");

            $postData = [
                'template' => $template_content,
                'template_id' => $system_template_id,
                'status' => $ct_status
            ];

            $result = $this->configService->updateTemplate($company_template_id, $postData);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCConfigController][updateTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            Log::error("[eCConfigController][deleteTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
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
            Log::error("[eCConfigController][deleteMultiTemplate] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }


}
