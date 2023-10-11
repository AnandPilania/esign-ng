<?php

namespace Admin\Controllers;

use Admin\Services\CustomerService;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Helpers\Common;
use Customer\Exceptions\eCBusinessException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends eCBaseController
{

    private $customerService;

    /**
     * CustomerController constructor.
     * @param $customerService
     */
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function init(Request $request)
    {
        try {
            $result = $this->customerService->init();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[CustomerController][init] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function search(Request $request) {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->customerService->search($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[CustomerController][search] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function create(Request $request)
    {
        try {
            $name = $request->json("name");
            $agency_id = $request->json("agency_id");
            $tax_number = $request->json("tax_number");
            $fax_number = $request->json("fax_number");
            $address = $request->json("address");
            $status = $request->json("status");
            $phone = $request->json("phone");
            $email = $request->json("email");
            $service_id = $request->json("service_id");
            $representative = $request->json("representative");
            $source_method = $request->json("source_method");
            $password = $request->json("password");
            $sendEmail = $request->json("option");

            $postData = [
                'name' => $name,
                'agency_id' => $agency_id,
                'tax_number' => $tax_number,
                'fax_number' => $fax_number,
                'address' => $address,
                'password' => $password,
                'sendEmail' => $sendEmail,
                'status' => $status,
                'phone' => $phone,
                'email' => $email,
                'service_id' => $service_id,
                'representative' => $representative,
                'source_method' => $source_method,
            ];

            $rules = [
                'name' => 'required|max:255',
                'agency_id' => 'nullable|exists:ec_agencies,id',
                'tax_number' => 'required|max:30',
                'fax_number' => 'nullable|max:50',
                'address' => 'required|max:255',
                'phone' => 'required|max:30',
                'email' => 'required|max:255',
                'service_id' => 'required|exists:s_service_config,id',
                'representative' => 'required|max:255',
                'source_method' => 'required',
                'status' => 'boolean',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->customerService->create($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[CustomerController][create] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->json('id');
            $name = $request->json("name");
            $tax_number = $request->json("tax_number");
            $fax_number = $request->json("fax_number");
            $address = $request->json("address");
            $status = $request->json("status");
            $phone = $request->json("phone");
            $email = $request->json("email");
            $service_id = $request->json("service_id");
            $representative = $request->json("representative");
            $source_method = $request->json("source_method");

            $postData = [
                'id' => $id,
                'name' => $name,
                'tax_number' => $tax_number,
                'fax_number' => $fax_number,
                'address' => $address,
                'status' => $status,
                'phone' => $phone,
                'email' => $email,
                'service_id' => $service_id,
                'representative' => $representative,
                'source_method' => $source_method,
            ];

            $rules = [
                'id' => 'required|max:255',
                'name' => 'required|max:255',
                'tax_number' => 'required|max:30',
                'fax_number' => 'nullable|max:50',
                'address' => 'required|max:255',
                'phone' => 'required|max:30',
                'email' => 'required|max:255',
                'representative' => 'required|max:255',
                'service_id' => 'required|exists:s_service_config,id',
                'source_method' => 'required',
                'status' => 'boolean',
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) {
                throw new eCBusinessException('SERVER.INVALID_INPUT');
            }
            $result = $this->customerService->update($postData);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[CustomerController][update] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function delete(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->customerService->delete([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[CustomerController][delete] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMulti(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->customerService->delete($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[CustomerController][deleteMulti] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function changePassword(Request $request){
        try {
            $result = $this->customerService->changePasswordCustomer($request);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e){
            Log::error("[CustomerController][changePassword] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function getServiceDetail(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->customerService->getServiceDetail($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[CustomerController][getServiceDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function getDocumentList(Request $request){
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->customerService->searchDocumentList($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[CustomerController][getServiceDetail] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function getDataConfigCompany(Request $request){
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->customerService->getDataConfigCompany($id);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[CustomerController][getDataConfigCompany] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }


    public function updateConfigCompany(Request $request)
    {
        try {
            $company_id = $request->company_id;
            $theme_header_color = $request->theme_header_color;
            $theme_footer_color = $request->theme_footer_color;
            $step_color = $request->step_color;
            $text_color = $request->text_color;
            $name_app = $request->name_app;
            $logo_sign = $request->logo_sign;
            $logo_login = $request->logo_login;
            $logo_background = $request->logo_background;
            $fa_icon = $request->fa_icon;
            $loading = $request->loading;
            $logo_dashboard = $request->logo_dashboard;
            $file_size_upload = $request->file_size_upload;

            $postData = [
                "company_id" => $company_id,
                "theme_header_color" => $theme_header_color,
                "theme_footer_color" => $theme_footer_color,
                "file_size_upload" => $file_size_upload,
                "step_color" => $step_color,
                "text_color" => $text_color,
                "name_app" => $name_app,
                "logo_sign" => $logo_sign,
                "logo_login" => $logo_login,
                "logo_background" => $logo_background,
                "fa_icon" => $fa_icon,
                "loading" => $loading,
                "logo_dashboard" => $logo_dashboard,
            ];

            $rules = [
                "company_id" => "required",
                "theme_header_color" => "required",
                "theme_footer_color" => "required",
                "file_size_upload" => "required",
                "step_color" => "required",
                "text_color" => "required",
                "name_app" => "required",
                "logo_sign" => "required",
                "logo_login" => "required",
                "logo_background" => "required",
                "fa_icon" => "required",
                "loading" => "required",
                "logo_dashboard" => "required",
            ];

            $validator = Validator::make($postData, $rules);
            if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');
            $result = $this->customerService->updateConfigCompany($postData);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[CustomerController][updateConfigCompany] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
    public function reNewService(Request $request)
    {
        try {
            $id = $request->json('id');
            $result = $this->customerService->reNewService($id);
            return $this->sendResponse($result, 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[CustomerController][updateConfigCompany] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

}
