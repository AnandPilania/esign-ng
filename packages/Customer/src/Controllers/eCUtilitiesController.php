<?php

namespace Customer\Controllers;

use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Models\eCCustomers;
use Core\Models\eCDepartments;
use Core\Models\eCDocumentTypes;
use Core\Models\eCEmployee;
use Core\Models\eCPositions;
use Customer\Services\eCUtilitiesService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class eCUtilitiesController extends eCBaseController
{
    private $utilitiesService;

    /**
     * eCUtilitiesController constructor.
     * @param $utilitiesService
     */
    public function __construct(eCUtilitiesService $utilitiesService)
    {
        $this->utilitiesService = $utilitiesService;
    }

    public function initPosition()
    {
        try {
            $result = $this->utilitiesService->initPositionSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][initPosition] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchChucVu(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->utilitiesService->searchPosition($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][searchChucVu] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewPosition(Request $request)
    {
        try {
            $position_code = $request->json("position_code");
            $name = $request->json("name");
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'position_code' => $position_code,
                'name' => $name,
                'note' => $note,
                'status' => $status
            ];

            $result = $this->utilitiesService->insertPosition($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][createNewPosition] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updatePosition(Request $request)
    {
        try {
            $id = $request->json("id");
            $position_code = $request->json("position_code");
            $name = $request->json("name");
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'position_code' => $position_code,
                'name' => $name,
                'note' => $note,
                'status' => $status
            ];

            $result = $this->utilitiesService->updatePosition($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][updatePosition] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deletePosition(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->deletePositionSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][deletePosition] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiPosition(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->utilitiesService->deletePositionSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][deleteMultiPosition] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initDepartment()
    {
        try {
            $result = $this->utilitiesService->initDepartmentSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][initDepartment] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchPhongBan(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->utilitiesService->searchDepartment($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][searchPhongBan] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewDepartment(Request $request)
    {
        try {
            $department_code = $request->json("department_code");
            $name = $request->json("name");
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'department_code' => $department_code,
                'name' => $name,
                'note' => $note,
                'status' => $status
            ];

            $result = $this->utilitiesService->insertDepartment($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][createNewDepartment] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateDepartment(Request $request)
    {
        try {
            $id = $request->json("id");
            $department_code = $request->json("department_code");
            $name = $request->json("name");
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'department_code' => $department_code,
                'name' => $name,
                'note' => $note,
                'status' => $status
            ];

            $result = $this->utilitiesService->updateDepartment($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][updateDepartment] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    //TODO: Using service for business layer
    public function deleteDepartment(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->deleteDepartmentSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][deleteDepartment] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiDepartment(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->utilitiesService->deleteDepartmentSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][deleteMultiDepartment] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initEmployee(Request $request)
    {
        try {
            $result = $this->utilitiesService->initEmployeeSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][initEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchEmployee(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->utilitiesService->searchEmployee($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][searchEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewEmployee(Request $request)
    {
        try {

            $emp_code = $request->json("emp_code");
            $reference_code = $request->json("reference_code");
            $emp_name = $request->json("emp_name");
            $dob = $request->json("birthday");
            $sex = $request->json("sex");
            $address1 = $request->json("address1");
            $address2 = $request->json("address2");
            $ethnic = $request->json("ethnic");
            $nationality = $request->json("nationality");
            $national_id = $request->json("national_id");
            $national_date = $request->json("nationalDate");
            $national_address_provide = $request->json("national_address_provide");
            $email = $request->json("email");
            $phone = $request->json("phone");
            $department_id = $request->json("department_id");
            $position_id = $request->json("position_id");
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'department_id' => $department_id,
                'position_id' => $position_id,
                'emp_code' => $emp_code,
                'emp_name' => $emp_name,
                'reference_code' => $reference_code,
                'dob' => $dob,
                'sex' => $sex,
                'ethnic' => $ethnic,
                'nationality' => $nationality,
                'address1' => $address1,
                'address2' => $address2,
                'national_id' => $national_id,
                'national_date' => $national_date,
                'national_address_provide' => $national_address_provide,
                'email' => $email,
                'phone' => $phone,
                'note' => $note,
                'status' => $status,
            ];
            $result = $this->utilitiesService->insertEmployee($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][createNewEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateEmployee(Request $request)
    {
        try {

            $id = $request->json("id");

            $emp_code = $request->json("emp_code");
            $reference_code = $request->json("reference_code");
            $emp_name = $request->json("emp_name");
            $dob = $request->json("birthday");
            $sex = $request->json("sex");
            $address1 = $request->json("address1");
            $address2 = $request->json("address2");
            $ethnic = $request->json("ethnic");
            $nationality = $request->json("nationality");
            $national_id = $request->json("national_id");
            $national_date = $request->json("nationalDate");
            $national_address_provide = $request->json("national_address_provide");
            $email = $request->json("email");
            $phone = $request->json("phone");
            $department_id = $request->json("department_id");
            $position_id = $request->json("position_id");
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'department_id' => $department_id,
                'position_id' => $position_id,
                'emp_code' => $emp_code,
                'emp_name' => $emp_name,
                'reference_code' => $reference_code,
                'dob' => $dob,
                'sex' => $sex,
                'ethnic' => $ethnic,
                'nationality' => $nationality,
                'address1' => $address1,
                'address2' => $address2,
                'national_id' => $national_id,
                'national_date' => $national_date,
                'national_address_provide' => $national_address_provide,
                'email' => $email,
                'phone' => $phone,
                'note' => $note,
                'status' => $status,
            ];

            $result = $this->utilitiesService->updateEmployee($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][updateEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteEmployee(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->deleteEmployeeSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][deleteEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiEmployee(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->utilitiesService->deleteEmployeeSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][deleteMultiEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function checkExistEmployee(Request $request)
    {
        try {
            $email = $request->json("email");
            $postData = [
                'email' => $email
            ];
            $result = $this->utilitiesService->checkExistEmployee($postData);
            return $this->sendResponse($result, 'SERVER.CHECK_EXIST_EMPLOYEE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][checkExistEmployee] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initCustomer()
    {
        try {
            $result = $this->utilitiesService->initCustomerSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][initCustomer] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchKhachHangDoiTac(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->utilitiesService->searchCustomer($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][searchKhachHangDoiTac] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewCustomer(Request $request)
    {
        try {
            $code = $request->json("code");
            $name = $request->json("name");
            $address = $request->json('address');
            $phone = $request->json('phone');
            $email = $request->json('email');
            $tax_number = $request->json('tax_number');
            $contact_name = $request->json('contact_name');
            $contact_phone = $request->json('contact_phone');
            $representative = $request->json('representative');
            $representative_position = $request->json('representative_position');
            $bank_info = $request->json('bank_info');
            $bank_account = $request->json('bank_account');
            $bank_number = $request->json('bank_number');
            $customer_type = $request->json('customer_type');
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'tax_number' => $tax_number,
                // 'representative' => $representative,
                // 'representative_position' => $representative_position,
                // 'contact_name' => $contact_name,
                // 'contact_phone' => $contact_phone,
                'bank_info' => $bank_info,
                // 'bank_account' => $bank_account,
                'bank_number' => $bank_number,
                // 'customer_type' => $customer_type,
                'note' => $note,
                'status' => $status,
            ];

            $result = $this->utilitiesService->insertCustomer($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][createNewCustomer] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateCustomer(Request $request)
    {
        try {
            $id = $request->json("id");
            $code = $request->json("code");
            $name = $request->json("name");
            $address = $request->json('address');
            $phone = $request->json('phone');
            $email = $request->json('email');
            $tax_number = $request->json('tax_number');
            $contact_name = $request->json('contact_name');
            $contact_phone = $request->json('contact_phone');
            $representative = $request->json('representative');
            $representative_position = $request->json('representative_position');
            $bank_info = $request->json('bank_info');
            $bank_account = $request->json('bank_account');
            $bank_number = $request->json('bank_number');
            $customer_type = $request->json('customer_type');
            $note = $request->json("note");
            $status = $request->json("status");

            $postData = [
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'tax_number' => $tax_number,
                'representative' => $representative,
                'representative_position' => $representative_position,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'bank_info' => $bank_info,
                'bank_account' => $bank_account,
                'bank_number' => $bank_number,
                'customer_type' => $customer_type,
                'note' => $note,
                'status' => $status,
            ];
            $result = $this->utilitiesService->updateCustomer($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][updateCustomer] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteCustomer(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->deleteCustomerSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][deleteCustomer] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiCustomer(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->utilitiesService->deleteCustomerSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][deleteMultiCustomer] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function checkExistCustomer(Request $request)
    {
        try {
            $email = $request->json("email");

            $postData = [
                'email' => $email
            ];

            $result = $this->utilitiesService->checkExistCustomer($postData);
            return $this->sendResponse($result, 'SERVER.CHECK_EXIST_CUSTOMER_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][checkExistCustomer] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initDocumentType(Request $request)
    {
        try {
            $result = $this->utilitiesService->initDocumentTypeSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][initDocumentType] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchPhanLoaiTaiLieu(Request $request)
    {
        try {

            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->utilitiesService->searchDocumentType($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][searchKhachHangDoiTac] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewDocumentType(Request $request)
    {
        try {
            $dc_type_code = $request->json("dc_type_code");
            $dc_type_name = $request->json("dc_type_name");
            $dc_style = $request -> json("dc_style");
            $note = $request->json("note");
            $status = $request->json("status");
            $is_order_auto = $request->json("is_order_auto");
            $is_auto_reset = $request->json("is_auto_reset");
            $dc_length = $request->json("dc_length");
            $dc_format = $request->json("dc_format");
            $document_group_id = $request->json("document_group_id");

            $postData = [
                'dc_type_code' => $dc_type_code,
                'dc_type_name' => $dc_type_name,
                'dc_style' => $dc_style,
                'note' => $note,
                'status' => $status,
                'is_order_auto' => $is_order_auto,
                'is_auto_reset' => $is_auto_reset,
                'dc_length' => $dc_length,
                'dc_format' => $dc_format,
                'document_group_id' => $document_group_id,
            ];

            $result = $this->utilitiesService->insertDocumentType($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][createNewDocumentType] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateDocumentType(Request $request)
    {
        try {

            $id = $request->json("id");
            $dc_type_code = $request->json("dc_type_code");
            $dc_type_name = $request->json("dc_type_name");
            $dc_style = $request -> json("dc_style");
            $note = $request->json("note");
            $status = $request->json("status");
            $is_order_auto = $request->json("is_order_auto");
            $is_auto_reset = $request->json("is_auto_reset");
            $dc_length = $request->json("dc_length");
            $dc_format = $request->json("dc_format");
            $document_group_id = $request->json("document_group_id");

            $postData = [
                'dc_type_code' => $dc_type_code,
                'dc_type_name' => $dc_type_name,
                'dc_style' => $dc_style,
                'note' => $note,
                'status' => $status,
                'is_order_auto' => $is_order_auto,
                'is_auto_reset' => $is_auto_reset,
                'dc_length' => $dc_length,
                'dc_format' => $dc_format,
                'document_group_id' => $document_group_id,

            ];
            $result = $this->utilitiesService->updateDocumentType($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][updateDocumentType] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteDocumentType(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->deleteDocumentTypeSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][deleteDocumentType] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiDocumentType(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->utilitiesService->deleteDocumentTypeSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][deleteMultiDocumentType] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function initBranch()
    {
        try {
            $result = $this->utilitiesService->initBranchSetting();
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][initBranch] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function searchChiNhanh(Request $request)
    {
        try {
            $draw = $request->json("draw");
            $start = $request->json("start");
            $limit = $request->json("limit");
            $order = $request->json("order");
            $sortQuery = $order["columnName"] . " " . $order["dir"];
            $searchData = $request->json("searchData");

            $result = $this->utilitiesService->searchBranch($searchData, $draw, $start, $limit, $sortQuery);
            return $this->sendResponse($result, '');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][searchChiNhanh] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function createNewBranch(Request $request)
    {
        try {
            $name = $request->json("name");
            $tax_number = $request->json('tax_number');
            $address = $request->json('address');
            $branch_code = $request->json('branch_code');
            $phone = $request->json('phone');
            $email = $request->json('email');
            $status = $request->json("status");

            $postData = [
                'name' => $name,
                'status' => $status,
                'email' => $email,
                'branch_code' => $branch_code,
                'tax_number' => $tax_number,
                'address' => $address,
                'phone' => $phone,
            ];

            $result = $this->utilitiesService->insertBranch($postData);
            return $this->sendResponse($result, 'SERVER.INSERT_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][createNewBranch] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function updateBranch(Request $request)
    {
        try {
            $id = $request->json("id");
            $name = $request->json("name");
            $tax_number = $request->json('tax_number');
            $branch_code = $request->json('branch_code');
            $address = $request->json('address');
            $phone = $request->json('phone');
            $email = $request->json('email');
            $status = $request->json("status");

            $postData = [
                'name' => $name,
                'status' => $status,
                'email' => $email,
                'branch_code' => $branch_code,
                'tax_number' => $tax_number,
                'address' => $address,
                'phone' => $phone,
            ];

            $result = $this->utilitiesService->updateBranch($id, $postData);
            return $this->sendResponse([], 'SERVER.UPDATE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][updateBranch] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteBranch(Request $request)
    {
        try {
            $id = $request->json("id");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->deleteBranchSetting([$id]);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][deleteBranch] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function deleteMultiBranch(Request $request)
    {
        try {
            $ids = $request->json("lst");
            if (!isset($ids) || count($ids) == 0) {
                return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            }
            $result = $this->utilitiesService->deleteBranchSetting($ids);
            return $this->sendResponse($result, 'SERVER.DELETE_SUCCESSFUL');
        } catch (Exception $e) {
            Log::error("[eCUtilitiesController][deleteMultiBranch] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }

    public function changeBranchStatus(Request $request){
        try {
            $id = $request->json("id");
            $status = $request->json("status");
            if (!isset($id)) return $this->sendError(ApiHttpStatus::BAD_REQUEST, 'SERVER.INVALID_INPUT');
            $result = $this->utilitiesService->changeBranchStatus($id, $status);
            if($status == 0) {
                return $this->sendResponse($result, 'SERVER.PAUSE_BRANCH_SUCCESSFUL');
            } else {
                return $this->sendResponse($result, 'SERVER.ACTIVE_BRANCH_SUCCESSFUL');
            }
        } catch (Exception $e) {
            //TODO: Should use global handle for this.
            Log::error("[eCUtilitiesController][changeBranchStatus] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->handleException($e);
        }
    }
}
