<?php


namespace Customer\Services;

use Core\Helpers\AssigneeState;
use Core\Helpers\AssigneeType;
use Core\Helpers\DocumentLogStatus;
use Core\Helpers\DocumentState;
use Core\Helpers\NotificationType;
use Core\Helpers\StorageHelper;
use Core\Helpers\Common;
use Core\Helpers\DocumentType;
use Core\Helpers\DocumentStyle;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Helpers\AddendumType;
use Core\Helpers\ImageHelper;
use Core\Models\eCCompany;
use Core\Models\eCCompanySignature;
use Core\Models\eCConfigParams;
use Core\Models\eCCustomers;
use Core\Models\eCDocumentAssignee;
use Core\Models\eCDocumentLogs;
use Core\Models\eCDocumentPartners;
use Core\Models\eCDocumentResources;
use Core\Models\eCDocumentResourcesEx;
use Core\Models\eCDocumentSample;
use Core\Models\eCDocumentSampleInfo;
use Core\Models\eCDocumentSignature;
use Core\Models\eCDocumentSignatureKyc;
use Core\Models\eCDocumentTextInfo;
use Core\Models\eCDocumentTypes;
use Core\Models\eCEmployee;
use Core\Models\eCUserSignature;
use Core\Models\eCVerifyTransactionLogs;
use Core\Models\eCDocumentHsm;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Customer\Services\Shared\eCDocumentHandlingService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Core\Models\eCDocuments;
use Core\Models\ecDocumentConversations;
use Maatwebsite\Excel\Concerns\ToArray;
use Core\Models\eCUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Core\Services\ActionHistoryService;

class eCDocumentService
{
    private $permissionService;
    private $storageHelper;
    private $imageHelper;
    private $documentHandlingService;
    private $actionHistoryService;

    /**
     * eCUtilitiesService constructor.
     * @param eCPermissionService $permissionService
     * @param StorageHelper $storageHelper
     * @param ImageHelper $imageHelper
     * @param eCDocumentHandlingService $documentHandlingService
     */
    public function __construct(eCPermissionService $permissionService, StorageHelper $storageHelper, eCDocumentHandlingService $documentHandlingService, ActionHistoryService $actionHistoryService, ImageHelper $imageHelper)
    {
        $this->permissionService = $permissionService;
        $this->storageHelper = $storageHelper;
        $this->documentHandlingService = $documentHandlingService;
        $this->actionHistoryService = $actionHistoryService;
        $this->imageHelper = $imageHelper;
    }

    public function initCreateDocument($id, $documentType, $document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_CREATE" : "COMMERCE_CREATE");
        if (!$permission || $permission->is_view != 1 || $permission->is_write != 1) {
            throw new eCAuthenticationException();
        }
        $checkExpired = $this->permissionService->checkExpired($user->company_id);
        if (!$checkExpired && !isset($id)) {
            throw new eCBusinessException("SERVER.EXPIRED");
        }
        $company = eCCompany::where('id', $user->company_id)
            ->select('name', 'tax_number')
            ->first();
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('document_group_id', $documentType)
            ->where('status', 1)
            ->get();
        $lstDocumentSample = eCDocumentSample::where('company_id', $user->company_id)
            ->where('document_type', $documentType)
            ->get();
        $config = eCConfigParams::where('company_id', $user->company_id)->first();
        $lstEmployee = eCEmployee::where('company_id', $user->company_id)
            ->where('status', 1)
            ->select('id', 'emp_name as name', 'email' , 'phone')
            ->get();
        $lstCustomer = eCCustomers::where('company_id', $user->company_id)
            ->where('status', 1)
            ->select('id', 'name', 'email' , 'phone', 'customer_type')
            ->get();
        $document = null;
        if (isset($id)) {
            $document = eCDocuments::where('id', $id)->where('company_id', $user->company_id)->first();
            if (!$document) {
                throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
            }
            if ($document->document_state != DocumentState::DRAFT) {
                throw new eCBusinessException("SERVER.NOT_DRAFT_STATE");
            }
            if ($user->is_personal) {
                if ($user->id != $document->created_by) {
                    throw new eCBusinessException("SERVER.NOT_PERMISSION_EDIT_DOCUMENT");
                }
            } else {
                if ($document->branch_id != $user->branch_id) {
                    throw new eCBusinessException("SERVER.NOT_PERMISSION_EDIT_DOCUMENT");
                }
            }
            $document->files = eCDocumentResources::where('document_id', $id)
                ->where('status', 1)
                ->get();
            $document->partners = eCDocumentPartners::where('document_id', $id)
                ->where('status', 1)
                ->orderBy("order_assignee", "asc")->get();
            foreach ($document->partners as $partner) {
                $partner->assignees = eCDocumentAssignee::where('partner_id', $partner->id)
                    ->whereIn('assign_type', [AssigneeType::APPROVAL, AssigneeType::SIGN, AssigneeType::STORAGE])
                    ->where('status', 1)
                    ->get();
            }
            $docPath = eCDocumentResourcesEx::where('document_id', $id)->where('status', 1)->first();
            $document->save_password = isset($docPath->save_password) && $document->is_encrypt ? $docPath->save_password : "";
        }
        return array('permission' => $permission, 'documentType' => $lstDocumentType, 'documentSample' => $lstDocumentSample, 'document' => $document, 'company' => $company, 'config' => $config, 'lstEmployee' => $lstEmployee, 'lstCustomer' => $lstCustomer, 'checkExpired' => $checkExpired);
    }


    public function getDocumentCodeByDocumentTypeId($id, $documentType)
    {
        $user = Auth::user();
        $documentType = eCDocumentTypes::where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('document_group_id', $documentType)
            ->where('status', 1)
            ->first();
        if (!$documentType) throw new eCBusinessException('SERVER.DOCUMENT_TYPE_NOT_EXISTED');

        $lastDoc = eCDocuments::where('document_type_id', $id)
            ->where('parent_id', -1)
            ->orderBy('id', 'desc')
            ->select('code')
            ->first();

        if ($documentType->is_order_auto == 0) {
            return "-1";
        }

        $code = $codeNoReset = $documentType->dc_format;

        $code = str_replace("{YY}", date('y'), $code);
        $code = str_replace("{MM}", date('m'), $code);
        $code = str_replace("{YYYY}", date('Y'), $code);
        $code = str_replace("{code}", $documentType->dc_type_code, $code);
        $codeNoReset = str_replace("{code}", $documentType->dc_type_code, $codeNoReset);
        $code = str_replace("\/", "/", $code);

        if (!$lastDoc) {
            $lastDoc = '';
        }
        $code = $this->documentHandlingService->getCode($code, $codeNoReset, $lastDoc, $documentType);
        return $code;
    }

    public function getDetailDocumentSampleById($id, $documentType)
    {
        $user = Auth::user();
        $documentSample = eCDocumentSample::where('id', $id)
            ->where('document_type', $documentType)
            ->first();
        if (!$documentSample) throw new eCBusinessException('SERVER.DOCUMENT_SAMPLE_NOT_EXISTED');

        $isOrderAuto = eCDocumentTypes::where('id', $documentSample->document_type_id)
            ->where('company_id', $user->company_id)
            ->where('document_group_id', $documentType)
            ->where('status', 1)
            ->first();
        $sampleInfo = eCDocumentSampleInfo::where('document_sample_id', $id)
            ->orderBy('order_assignee', 'asc')
            ->get();
        $signatures = [];
        $infos = [];
        foreach ($sampleInfo as $info) {
            if ($info->data_type == 1) {
                $text = array(
                    "id" => $info->id,
                    "type" => "text",
                    "form_name" => $info->form_name,
                    "description" => $info->description,
                    "is_required" => $info->is_required == 1,
                    "is_editable" => $info->is_editable == 1,
                );
                array_push($infos, $text);
            } else if ($info->data_type == 2) {
                $signature = array(
                    "id" => $info->id,
                    "description" => $info->description,
                    "is_editable" => $info->is_editable == 1,
                    "full_name" => $info->full_name,
                    "email" => $info->email,
                    "phone" => $info->phone,
                    "national_id" => $info->national_id,
                    "image_signature" => $info->image_signature,
                    "sign_method" => $info->sign_method,
                    "order_assignee" => $info->order_assignee,
                    "is_my_organisation" => $info->is_my_organisation
                );
                array_push($signatures, $signature);
            } else if ($info->data_type == 3) {
                $isExisted = false;
                for ($i = 0; $i < count($infos); $i++) {
                    if ($infos[$i]["form_name"] == $info->form_name) {
                        array_push($infos[$i]["elements"], array(
                            "id" => $info->id,
                            "description" => $info->description,
                        ));
                        $isExisted = true;
                    }
                }
                if (!$isExisted) {
                    $checkbox = array(
                        "type" => "checkbox",
                        "form_name" => $info->form_name,
                        "description" => $info->form_description,
                        "is_required" => $info->is_required == 1,
                        "is_editable" => $info->is_editable == 1,
                        "elements" => []
                    );
                    array_push($checkbox["elements"], array(
                        "id" => $info->id,
                        "description" => $info->description,
                    ));
                    array_push($infos, $checkbox);
                }
            } else if ($info->data_type == 4) {
                $isExisted = false;
                for ($i = 0; $i < count($infos); $i++) {
                    if ($infos[$i]["form_name"] == $info->form_name) {
                        array_push($infos[$i]["elements"], array(
                            "id" => $info->id,
                            "description" => $info->description,
                        ));
                        $isExisted = true;
                    }
                }
                if (!$isExisted) {
                    $checkbox = array(
                        "type" => "radio",
                        "form_name" => $info->form_name,
                        "description" => $info->form_description,
                        "is_required" => $info->is_required == 1,
                        "is_editable" => $info->is_editable == 1,
                        "elements" => []
                    );
                    array_push($checkbox["elements"], array(
                        "id" => $info->id,
                        "description" => $info->description,
                    ));
                    array_push($infos, $checkbox);
                }
            }
        }
        return array('documentSample' => $documentSample, "infos" => $infos, "signatures" => $signatures, 'is_order_auto' => $isOrderAuto);
    }

    public function getSignDocument($id, $ip)
    {
        $user = Auth::user();
        $doc = eCDocuments::where('id', $id)->where('company_id', $user->company_id)->first();

        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        try {
            // get giải mã file mã hóa
            if ($doc->is_encrypt) {
                $url = Common::getConverterServer() . '/api/v1/decryptPDF';
                $data = array(
                    "document_id" => $id,
                );
                $fileResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
                if (!$fileResponse['status'])  throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
                $fileResponse= json_decode($fileResponse['data']);
                return base64_decode($fileResponse->data);
//                return $fileResponse;
            } else {
                $file = eCDocumentResourcesEx::where('document_id', $id)->where('status', 1)->first();
                return $this->storageHelper->downloadFile($file->document_path_sign);
            }
        } catch (Exception $e) {
            throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
        }
    }

    public function getHistoryDocument($id)
    {
        $user = Auth::user();
        $doc = eCDocuments::where('id', $id)->where('company_id', $user->company_id)->first();

        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        $history = eCDocumentLogs::where('document_id', $id)->orderBy('id', 'asc')->get();

        return $history;
    }

    public function getHistoryTransactionDocument($id)
    {
        $user = Auth::user();
        $doc = eCDocuments::where('id', $id)->where('company_id', $user->company_id)->first();

        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if (!$doc->transaction_id) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        $history = eCVerifyTransactionLogs::where('transaction_id', $doc->transaction_id)->get();

        return $history;
    }

    public function uploadFiles($request, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_CREATE" : "COMMERCE_CREATE", false, true, false, false);
        if (!$hasPermission)  throw new eCAuthenticationException();
        $id = $request->get("id") == "undefined" ? null : $request->get("id");
        $document_type_id = $request->document_type_id;
        $code = $request->code;
        $name = $request->name;
        $sent_date = $request->sent_date;
        $expired_date = $request->expired_date;
        $parent_id = $request->parent_id;
        $addendum_type = $request->addendum_type;
        $expired_type = $request->expired_type;
        $doc_expired_date = $request->doc_expired_date;
        $expired_month = $request->expired_month;
        $is_verify_content = $request->is_verify_content == "true";
        $is_encrypt = $request->is_encrypt;
        $save_password = $request->encrypt_password;

        $postData = [
            'parent_id' => $parent_id,
            'document_type_id' => $document_type_id,
            'code' => $code,
            'name' => $name,
            'sent_date' => $sent_date,
            'expired_date' => $expired_date,
            'addendum_type' => $addendum_type,
            'expired_type' => $expired_type,
            'doc_expired_date' => $doc_expired_date,
            'expired_month' => $expired_month,
            'is_verify_content' => $is_verify_content,
        ];

        $rules = [
            'document_type_id' => 'required_if:parent_id,==,-1|exists:s_document_types,id',
            'name' => 'required|max:255',
            'code' => 'required|max:50',
            'sent_date' => 'required|date',
            'expired_date' => 'required|date',
            'expired_type' => 'required',
            'doc_expired_date' => 'required_if:expired_type,1',
            'expired_month' => 'required_if:expired_type,2',
            'is_verify_content' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException($validator->errors());

        if (!$request->hasFile('files'))  throw new eCBusinessException('SERVER.INVALID_INPUT');

        $files = $request->file("files");
        $this->documentHandlingService->validFileUpload($files, $user->company_id);

        $queryExisted = eCDocuments::where('company_id', $user->company_id)
            ->where('code', $code);
        if (isset($id)) {
            $queryExisted->where('id', '!=', $id);
        }
        $existed_doc = $queryExisted->first();
        if ($existed_doc) throw new eCBusinessException("SERVER.EXISTED_DOCUMENT_CODE");

        if ($parent_id == -1) {
//            $documentType = eCDocumentTypes::where('id', $document_type_id)->where('status', 1)->first();
//            if ($documentType->is_order_auto == 1) {
//                $validateCode = $this->documentHandlingService->validateCode($code, $document_type_id, $documentType);
//                if (!$validateCode) throw new eCBusinessException("DOCUMENT.ERR_EMPTY_DOCUMENT_CODE_NUM");
//            }
        }
        DB::beginTransaction();
        try {
            $isUpdate = false;
            if (isset($id)) {
                $isUpdate = true;
                $doc = eCDocuments::find($id);
                if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != 1) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
                if ($doc->parent_id != -1) {
                    $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'UPDATE_ADDENDUM_STEP_1', $doc->code, json_encode($postData));
                } else {
                    $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'UPDATE_DOCUMENT_STEP_1', $doc->code, json_encode($postData));
                }
            } else {
                $documentType = eCDocumentTypes::where('id', $document_type_id)->first();
                $doc = new eCDocuments();
                $doc->document_type = $documentType->document_group_id;
                $doc->branch_id = $user->branch_id;
                $doc->company_id = $user->company_id;
                $doc->created_by = $user->id;
                $doc->document_draft_state = 1;
                $doc->status = 1;
                $doc->document_state = DocumentState::DRAFT;
                if ($doc->parent_id != -1) {
                    $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'INSERT_ADDENDUM_STEP_1', $doc->code, json_encode($postData));
                } else {
                    $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'INSERT_DOCUMENT_STEP_1', $doc->code, json_encode($postData));
                }
            }
            $doc->document_type_id = $document_type_id;
            $doc->code = $code;
            $doc->name = $name;
            $doc->sent_date = $sent_date;
            $doc->expired_date = $expired_date;
            $doc->parent_id = $parent_id;
            $doc->addendum_type = $addendum_type;
            $doc->expired_type = $expired_type;
            $doc->doc_expired_date = $doc_expired_date;
            $doc->expired_month = $expired_month;
            $doc->is_verify_content = $is_verify_content;
            $doc->is_encrypt = $is_encrypt ? true : false;
            $doc->updated_by = $user->id;
            $doc->save();
            $id = $doc->id;
            if (!$isUpdate){
                eCDocumentAssignee::create([
                    "company_id" => $user->company_id,
                    "full_name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "document_id" => $id,
                    "partner_id" => "-1",
                    "message" => "",
                    "noti_type" => 1,
                    "assign_type" => AssigneeType::CREATOR,
                    'state' => AssigneeState::COMPLETED, //da giao ket vi tao tai lieu thi da lien quan den tai lieu
                    "created_by" => $user->id
                ]);
                if ($doc->parent_id != -1) {
                    $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::CREATE_DOCUMENT, "CREATE_NEW_ADDENDUM", $user->name, $user->email);
                } else {
                    $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::CREATE_DOCUMENT, "CREATE_NEW_DOCUMENT", $user->name, $user->email);
                }
            }else{
                if ($doc->parent_id != -1) {
                    $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "REUPDATE_ADDENDUM", $user->name, $user->email);
                } else {
                    $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "REUPDATE_DOCUMENT", $user->name, $user->email);
                }
            }
            $total_fileid = array();
            $data_files = array();
            foreach ($files as $file) {
                //TODO: Call API to convert and merge pdf
                if ($document_group == DocumentType::INTERNAL) {
                    $path = $this->storageHelper->uploadFile($file, '/internal/' . $id . '/', false);
                } else if ($document_group == DocumentType::COMMERCE) {
                    $path = $this->storageHelper->uploadFile($file, '/commerce/' . $id . '/', false);
                } else {
                    throw new eCBusinessException('SERVER.INVALID_INPUT');
                }
                $file_id = explode(".", explode("/", $path)[3])[0];
                array_push($total_fileid, $file_id);
                array_push($data_files,[
                    "document_id" => $id,
                    "file_name_raw" => $file->getClientOriginalName(),
                    'file_type_raw' => $file->extension(),
                    'file_size_raw' => $file->getSize(),
                    "file_path_raw" => $path,
                    "file_id" => $file_id,
                    "created_by" => $user->id,
                    "updated_by" => $user->id,
                ]);
            }
            eCDocumentResources::insert($data_files);
            DB::commit();

            return array('id' => $id, 'total_fileid' => $total_fileid);
        } catch (Exception $e) {
            DB::rollback();
            if (str_contains($e, 'exists_code_unique')) {
                throw new eCBusinessException("SERVER.EXISTED_DOCUMENT_CODE");
            }
            throw $e;
        }
    }

    public function goStep2($request, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_CREATE" : "COMMERCE_CREATE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $ip = $request->ip();
        $id = $request->id;
        $document_type_id = $request->document_type_id;
        $code = $request->code;
        $name = $request->name;
        $sent_date = $request->sentDate;
        $expired_date = $request->expiredDate;
        $parent_id = $request->parent_id;
        $addendum_type = $request->addendum_type;
        $expired_type = $request->expired_type;
        $doc_expired_date = $request->docExpiredDate;
        $expired_month = $request->expired_month;
        $is_verify_content = $request->is_verify_content;
        $is_encrypt = $request->is_encrypt;
        $save_password = $request->encrypt_password;
        $postData = [
            'document_type_id' => $document_type_id,
            'code' => $code,
            'name' => $name,
            'sent_date' => $sent_date,
            'expired_date' => $expired_date,
            'parent_id' => $parent_id,
            'addendum_type' => $addendum_type,
            'expired_type' => $expired_type,
            'doc_expired_date' => $doc_expired_date,
            'expired_month' => $expired_month,
            'is_verify_content' => $is_verify_content,
        ];
        $rules = [
            'document_type_id' => 'required|exists:s_document_types,id',
            'name' => 'required|max:255',
            'code' => 'required|max:50',
            'sent_date' => 'required|date',
            'expired_date' => 'required|date',
            'expired_type' => 'required',
            'doc_expired_date' => 'required_if:expired_type,1',
            'expired_month' => 'required_if:expired_type,2',
            'is_verify_content' => 'boolean'
        ];
        $validator = Validator::make($postData, $rules);
        if ($validator->fails())  throw new eCBusinessException($validator->errors());

        $existedCode = eCDocuments::where('company_id', $user->company_id)
            ->where('code', $code)
            ->where('id', '!=', $id)
            ->first();
        if ($existedCode) throw new eCBusinessException("SERVER.EXISTED_DOCUMENT_CODE");


        DB::beginTransaction();
        try {

            eCDocuments::find($id)->update([
                'is_encrypt' => $is_encrypt ? true : false,
            ]);
            DB::commit();

            $url = Common::getConverterServer() . '/api/v1/document/merge';
            $data = array(
                "document_id" => $id,
                "password" => ($is_encrypt && $save_password) ? $save_password : Common::getConverterPassword()
            );
            $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            Log::info($mergeResponse);
            if ($mergeResponse["status"] != true) throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");

            $doc = eCDocuments::find($id);
            if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != 1) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

            $doc->document_type_id = $document_type_id;
            $doc->code = $code;
            $doc->name = $name;
            $doc->sent_date = $sent_date;
            $doc->expired_date = $expired_date;
            $doc->parent_id = $parent_id;
            $doc->addendum_type = $addendum_type;
            $doc->expired_type = $expired_type;
            $doc->doc_expired_date = $doc_expired_date;
            $doc->expired_month = $expired_month;
            $doc->is_verify_content = $is_verify_content;
            $doc->updated_by = $user->id;

            if ($doc->document_draft_state == 1) {
                $doc->document_draft_state = 2;
            }
            $doc->save();
            $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "EDIT_DOCUMENT", $user->name, $user->email);
            if ($doc->parent_id != -1) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'INSERT_ADDENDUM_STEP_2', $doc->code, json_encode($postData));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'INSERT_DOCUMENT_STEP_2', $doc->code, json_encode($postData));
            }
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function goStep3($request, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_CREATE" : "COMMERCE_CREATE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $id = $request->document_id;
        $partners = $request->partners;

        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != 1) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        DB::beginTransaction();
        try {
            eCDocumentPartners::where('document_id', $id)->where('status', 1)->delete();
            eCDocumentAssignee::where('document_id', $id)
                ->where('assign_type', '!=', AssigneeType::CREATOR)
                ->where('status', 1)->delete();
            foreach ($partners as $partner) {
                $dataAssignees = array();
                $partnerNew = eCDocumentPartners::create([
                    "document_id" => $id,
                    "order_assignee" => $partner["order_assignee"],
                    "organisation_type" => $partner["organisation_type"],
                    "company_name" => $partner["company_name"],
                    "tax" => $partner["tax"],
                    "created_by" => $user->id
                ]);
                foreach ($partner["assignees"] as $assignee) {
                    array_push($dataAssignees,[
                        "company_id" => $user->company_id,
                        "full_name" => $assignee["full_name"],
                        "email" => Str::lower($assignee["email"]),
                        "phone" => $assignee["phone"],
                        "document_id" => $id,
                        "message" => $assignee["message"],
                        "noti_type" => $assignee["noti_type"],
                        "is_auto_sign" => $assignee["is_auto_sign"],
                        "assign_type" => $assignee["assign_type"],
                        "national_id" => $assignee["national_id"],
                        "sign_method" => $assignee["sign_method"] ?? NULL,
                        "created_by" => $user->id
                    ]);
                }
                $partnerNew->assignee()->createMany($dataAssignees);
            }

            $doc->document_draft_state = 3;
            $doc->is_order_approval = $request->is_order_approval;
            $doc->updated_by = $user->id;
            $doc->save();
            if ($doc->parent_id != -1) {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "INSERT_LOG_ADDENDUM_ASSIGNEE", $user->name, $user->email);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'INSERT_ASSIGNEE_ADDENDUM', $doc->code, json_encode($partners));
            } else {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "INSERT_LOG_DOCUMENT_ASSIGNEE", $user->name, $user->email);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'INSERT_ASSIGNEE_DOCUMENT', $doc->code, json_encode($partners));
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        $res = eCDocumentPartners::where('document_id', $id)
            ->where('status', 1)
            ->orderBy("order_assignee", "asc")->get();
        foreach ($res as $partner) {
            $partner->assignees = eCDocumentAssignee::where('partner_id', $partner->id)
                ->where('status', 1)
                ->get();
        }

        return array('partners' => $res);
    }

    public function finishDrafting($request, $document_group)
    {
        $user = Auth::user();
        $ip = $request->ip();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_CREATE" : "COMMERCE_CREATE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $id = $request->document_id;
        $signatures = $request->signature;
        $texts = $request->texts;

        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::DRAFT) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        $signatureDatas = array();
        foreach ($signatures as $assignee) {
            foreach ($assignee["signatures"] as $signature) {
                array_push($signatureDatas, [
                    "document_id" => $id,
                    "assign_id" => $assignee["assign_id"],
                    "page_sign" => $signature["Page"],
                    "width_size" => $signature["Width"],
                    "height_size" => $signature["Height"],
                    "x" => $signature["XAxis"],
                    "y" => $signature["YAxis"],
                    "page_width" => $signature["pageWidth"],
                    "page_height" => $signature["pageHeight"],
                    "created_by" => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        $textDatas = array();
        foreach ($texts as $text) {
            array_push($textDatas, [
                "document_id" => $id,
                "matruong" => $text["matruong"],
                "content" => $text["noidung"],
                "font_size" => $text["size"],
                "font_style" => $text["FontFamily"],
                "page_sign" => $text["Page"],
                "width_size" => $text["Width"],
                "height_size" => $text["Height"],
                "page_width" => $signature["pageWidth"],
                "page_height" => $signature["pageHeight"],
                "x" => $text["XAxis"],
                "y" => $text["YAxis"],
                "created_by" => $user->id,
                'updated_by' => $user->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        DB::beginTransaction();
        try {
            eCDocumentSignature::where('document_id', $id)->delete();
            eCDocumentTextInfo::where('document_id', $id)->delete();
            eCDocumentSignature::insert($signatureDatas);
            eCDocumentTextInfo::insert($textDatas);
            if ($doc->parent_id != -1) {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "DESIGN_SIGNATURE_ADDENDUM", $user->name, $user->email);
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::FINISH_DRAFTING, "FINISH_DRAFTING_ADDENDUM_LOG", $user->name, $user->email);
            } else {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "DESIGN_SIGNATURE_DOCUMENT", $user->name, $user->email);
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::FINISH_DRAFTING, "FINISH_DRAFTING_DOCUMENT_LOG", $user->name, $user->email);
            }

            if ($doc->is_order_approval == 0) {
                $currentAssignee = $this->documentHandlingService->getNextAssignee($id, $user->company_id);
                $password = Common::randomString(6);
                $urlCode = Common::randomString(10) . "-" . time();
                if ($currentAssignee->assign_type == 1 || $currentAssignee->assign_type == 2) {
                    eCDocumentAssignee::where('id', $currentAssignee->id)
                        ->update([
                            "password" => Hash::make($password),
                            "url_code" => $urlCode
                        ]);
                }
                $sendParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $currentAssignee->message);
                // gọi hàm gửi mails
                if ($currentAssignee->assign_type == AssigneeType::SIGN && $currentAssignee->is_auto_sign) {
                    $doc->updated_by = $currentAssignee->id;
                    $doc->current_assignee_id = $currentAssignee->id;
                    $doc->save();
                    DB::commit();
                    $this->signDocumentHandling($doc, $user, $currentAssignee, 1, $ip);
                } else {
                    $this->documentHandlingService->sendNotifySign($currentAssignee, $doc, $sendParams);
                    $doc->updated_by = $user->id;
                    $doc->current_assignee_id = $currentAssignee->id;
                    $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $currentAssignee->full_name, $user->name, $user->email);
                }

            } else {
                $currentAssignee = $this->documentHandlingService->getNextAssignee($id, $user->company_id);
                if ($currentAssignee->assign_type == AssigneeType::APPROVAL) {
                    $doc->document_state = DocumentState::WAIT_APPROVAL;
                } else if ($currentAssignee->assign_type == AssigneeType::SIGN) {
                    $doc->document_state = DocumentState::WAIT_SIGNING;
                }

                $nextAssignees = $this->documentHandlingService->getAllAssigneeByType($id, $currentAssignee->assign_type);
                foreach ($nextAssignees as $assign) {
                    $password = Common::randomString(6);
                    $urlCode = Common::randomString(10) . "-" . time();
                    if ($assign->assign_type == 1 || $assign->assign_type == 2) {
                        eCDocumentAssignee::where('id', $assign->id)
                            ->update([
                                "password" => Hash::make($password),
                                "url_code" => $urlCode
                            ]);
                    }
                    $sendParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $assign->message);
                    if ($assign->assign_type == AssigneeType::SIGN && $assign->is_auto_sign) {
                        $doc->updated_by = $user->id;
                        $doc->current_assignee_id = $assign->id;
                        $doc->save();
                        DB::commit();
                        $this->signDocumentHandling($doc, $user, $assign, 1, $ip);
                    } else {
                        $this->documentHandlingService->sendNotifySign($assign, $doc, $sendParams);
                        $doc->updated_by = $user->id;
                        $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $assign->full_name, $user->name, $user->email);
                    }

                }
            }
            $myOrganization = eCDocumentPartners::where('document_id', $id)
                ->where('organisation_type', 1)
                ->first();
            $isExistStorageRole = eCDocumentPartners::join('ec_document_assignees as a', 'a.partner_id', 'ec_document_partners.id')
                ->where('ec_document_partners.document_id', $id)
                ->where('ec_document_partners.organisation_type', 1)
                ->where('a.assign_type', AssigneeType::STORAGE)
                ->first();
            if (!$isExistStorageRole) {
                $pushData = [
                    "company_id" => $user->company_id,
                    "full_name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "document_id" => $id,
                    "partner_id" => $myOrganization->id,
                    "message" => "",
                    "noti_type" => 1,
                    "assign_type" => AssigneeType::STORAGE,
                    "created_by" => $user->id
                ];
                eCDocumentAssignee::create($pushData);
            }
            DB::commit();
            if (count($textDatas) > 0) {
                $url = Common::getConverterServer() . '/api/v1/document/sign-text';
                $data = array(
                    "assign_id" => -1,
                    "document_id" => $id
                );
                $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
                Log::info($mergeResponse);
                if ($mergeResponse["status"] != true) throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
            }

            $doc->save();
            if ($doc->parent_id != -1) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'FINISH_DRAFTING_ADDENDUM', $doc->code, json_encode($doc));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'FINISH_DRAFTING_DOCUMENT', $doc->code, json_encode($doc));
            }
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function createDocumentFromTemplate($request, $document_group)
    {
        $user = Auth::user();
        $ip = $request->ip();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_CREATE" : "COMMERCE_CREATE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $isUseTemplate = $request->is_use_template;
        if (!$isUseTemplate) throw new eCBusinessException("SERVER.ERR_NOT_USE_TEMPLATE");

        $document_sample_id = $request->document_sample_id;
        $documentSample = eCDocumentSample::find($document_sample_id);
        if (!$documentSample) throw new eCBusinessException("SERVER.NOT_EXIST_TEMPLATE");

        $code = $request->code;
        $name = $request->name;
        $sent_date = $request->sentDate;
        $expired_date = $request->expiredDate;
        $expired_type = $documentSample->expired_type;
        $expired_month = $documentSample->expired_month;
        $is_verify_content = $request->is_verify_content == "true";
        $is_encrypt = $documentSample->is_encrypt;
        $save_password = $documentSample->save_password;
        $partners = $request->partners;
        $infos = $request->infos;

        $postData = [
            'document_sample_id' => $document_sample_id,
            'document_type_id' => $documentSample->document_type_id,
            'code' => $code,
            'name' => $name,
            'sent_date' => $sent_date,
            'expired_date' => $expired_date,
            'expired_type' => $expired_type,
            'expired_month' => $expired_month,
            'is_verify_content' => $is_verify_content,
            'updated_by' => $user->id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'document_type_id' => 'required|exists:s_document_types,id',
            'name' => 'required|max:255',
            'code' => 'required|max:50',
            'sent_date' => 'required|date',
            'expired_date' => 'required|date',
            'is_verify_content' => 'boolean'
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException($validator->errors());

        $existedCode = eCDocuments::where('company_id', $user->company_id)
            ->where('code', $code)
            ->get();
        if (count($existedCode) > 0) throw new eCBusinessException("SERVER.EXISTED_DOCUMENT_CODE");
        $documentType = eCDocumentTypes::where('id', $documentSample->document_type_id)
            ->where('status', 1)
            ->first();
//        if ($documentType->is_order_auto == 1) {
//            $validateCode = $this->documentHandlingService->validateCode($code, $documentSample->document_type_id, $documentType);
//            if (!$validateCode) {
//                throw new eCBusinessException("DOCUMENT.ERR_EMPTY_DOCUMENT_CODE_NUM");
//            }
//        }
        $sampleInfo = eCDocumentSampleInfo::where('document_sample_id', $document_sample_id)
            ->where('data_type', '!=', 2)
            ->get();
        $infoArr = array();
        foreach ($infos as $i) {
            $infoArr[$i['id']] = $i;
        }
        foreach ($sampleInfo as $info) {
            if ($info->is_required == 1) {
                $isExisted = false;
                if (array_key_exists($info->id, $infoArr)) {
                    if (isset($infoArr[$info->id]["content"]) && $infoArr[$info->id]["content"] != "") {
                        $isExisted = true;
                        break;
                    }
                }
                if (!$isExisted) throw new eCBusinessException($info->description . " Không được để trống");
            }
        }

        DB::beginTransaction();
        try {
            $doc = new eCDocuments();
            $doc->document_sample_id = $document_sample_id;
            $doc->document_type_id = $documentSample->document_type_id;
            $doc->document_type = $documentSample->document_type;
            $doc->branch_id = $user->branch_id;
            $doc->code = $code;
            $doc->name = $name;
            $doc->sent_date = $sent_date;
            $doc->expired_date = $expired_date;
            $doc->expired_type = $expired_type;
            $doc->expired_month = $expired_month;
            $doc->is_verify_content = $is_verify_content;
            $doc->updated_by = $user->id;
            $doc->company_id = $user->company_id;
            $doc->created_by = $user->id;
            $doc->status = 1;
            $doc->is_encrypt = $is_encrypt;
            $doc->document_state = DocumentState::DRAFT;
            $doc->save();
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'INSERT_DOCUMENT_BY_DOCUMENT_SAMPLE', $doc->code, json_encode($postData));

            $id = $doc->id;
            $name = $doc->name;

            eCDocumentAssignee::create([
                "company_id" => $user->company_id,
                "full_name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone,
                "document_id" => $id,
                "partner_id" => "-1",
                "message" => "",
                "noti_type" => 1,
                "assign_type" => AssigneeType::CREATOR,
                'state' => AssigneeState::COMPLETED, //da giao ket vi tao tai lieu thi da lien quan den tai lieu
                "created_by" => $user->id
            ]);

            $destinationPath = $document_group == DocumentType::INTERNAL ? '/internal/' . $id . '/' : '/commerce/' . $id . '/';
            $fileName = $is_encrypt ? time() . '-encrypt.pdf.enc' :  time() . '.pdf';

            $file = $this->storageHelper->copyFile($documentSample->document_path_original, $destinationPath, $fileName);

            eCDocumentResourcesEx::create([
                "company_id" => $user->company_id,
                "document_id" => $id,
                "parent_id" => -1,
                "document_path_original" => $destinationPath . $fileName,
                "document_path_sign" => $destinationPath . $fileName,
                "save_password" => $save_password,
                "created_by" => $user->id
            ]);

            $signatureDatas = array();
            $signatureImages = array();

            foreach ($partners as $partner) {
                $assigneeData = array();
                $partnerNew = eCDocumentPartners::create([
                    "document_id" => $id,
                    "order_assignee" => $partner["order_assignee"],
                    "organisation_type" => $partner["organisation_type"],
                    "company_name" => $partner["company_name"],
                    "tax" => $partner["tax"],
                    "created_by" => $user->id
                ]);

                foreach ($partner["assignees"] as $assignee) {
                    $signatureSample = eCDocumentSampleInfo::where('id', $assignee["signature_id"])->first();
                    $isAutoSign = ($signatureSample && $signatureSample->is_auto_sign == 1) ? 1 : 0;

                    $assignee_id = $partnerNew->assignee()->create([
                        "company_id" => $user->company_id,
                        "full_name" => $assignee["full_name"],
                        "email" => Str::lower($assignee["email"]),
                        "phone" => $assignee["phone"],
                        "message" => $assignee["message"],
                        "document_id" => $id,
                        "noti_type" => $assignee["noti_type"],
                        "assign_type" => $assignee["assign_type"],
                        "national_id" => $assignee["national_id"],
                        "sign_method" => $assignee["sign_method"] ?? NULL,
                        "is_auto_sign" => $isAutoSign,
                        "created_by" => $user->id
                    ])->id;

                    if ($signatureSample) {
                        array_push($signatureDatas, [
                            "document_id" => $id,
                            "assign_id" => $assignee_id,
                            "page_sign" => $signatureSample->page_sign,
                            "width_size" => $signatureSample->width_size,
                            "height_size" => $signatureSample->height_size,
                            "x" => $signatureSample->x,
                            "y" => $signatureSample->y,
                            "page_width" => $signatureSample->page_width,
                            "page_height" => $signatureSample->page_height,
                            "created_by" => $user->id,
                            'updated_by' => $user->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        if ($signatureSample->is_my_organisation == 1) {
                            array_push($signatureImages, [
                                "assign_id" => $assignee_id,
                                "image_signature" => $signatureSample->image_signature
                            ]);
                        }
                    }
                }
            }
            $textDatas = array();
            foreach ($sampleInfo as $info) {
                if ($info->is_editable == 0) {
                    $content = "";
                    if (isset($info->content)) {
                        $content = $info->content;
                    }
                    if ($info->field_code == "its_doc_code") {
                        $excode = explode("-", $code);
                        $content = $excode[0];
                    }
                    if ($info->field_code == "its_doc_time") {
                        if ($info->description) {
                            $docTime = $info->description;

                            $docTime = str_replace("{DD}", date('d'), $docTime);
                            $docTime = str_replace("{MM}", date('m'), $docTime);
                            $docTime = str_replace("{YY}", date('y'), $docTime);
                            $docTime = str_replace("{YYYY}", date('Y'), $docTime);
                            $docTime = str_replace("{H}", date('H'), $docTime);
                            $docTime = str_replace("{I}", date('i'), $docTime);
                            $docTime = str_replace("{S}", date('s'), $docTime);
                            $docTime = str_replace("/\s+/", ' ', $docTime);
                        }

                        $content = $docTime;
                    }
                    array_push($textDatas, [
                        "document_id" => $id,
                        "data_type" => $info->data_type,
                        "content" => $content,
                        "matruong" => $info->field_code,
                        "font_size" => isset($info->font_size) ? $info->font_size : "12",
                        "font_style" => isset($info->font_style) ? $info->font_style : "Times New Roman",
                        "page_sign" => $info->page_sign,
                        "width_size" => $info->width_size,
                        "height_size" => $info->height_size,
                        "page_width" => $info->page_width,
                        "page_height" => $info->page_height,
                        "x" => $info->x,
                        "y" => $info->y,
                        "created_by" => -1,
                        'updated_by' => -1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    if (array_key_exists($info->id, $infoArr)) {
                        $userInfo = $infoArr[$info->id];
                        array_push($textDatas, [
                            "document_id" => $id,
                            "data_type" => $info->data_type,
                            "content" => isset($userInfo["content"]) ? $userInfo["content"] : "",
                            "matruong" => $info->field_code,
                            "font_size" => isset($info->font_size) ? $info->font_size : "12",
                            "font_style" => isset($info->font_style) ? $info->font_style : "Times New Roman",
                            "page_sign" => $info->page_sign,
                            "width_size" => $info->width_size,
                            "height_size" => $info->height_size,
                            "page_width" => $info->page_width,
                            "page_height" => $info->page_height,
                            "x" => $info->x,
                            "y" => $info->y,
                            "created_by" => -1,
                            'updated_by' => -1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            eCDocumentSignature::insert($signatureDatas);
            eCDocumentTextInfo::insert($textDatas);
            eCDocumentSignatureKyc::insert($signatureImages);

            if ($doc->parent_id != -1) {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::FINISH_DRAFTING, "FINISH_DRAFTING_ADDENDUM_LOG", $user->name, $user->email);
            } else {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::FINISH_DRAFTING, "FINISH_DRAFTING_DOCUMENT_LOG", $user->name, $user->email);
            }

            DB::commit();

            if (count($textDatas) > 0) {
                $url = Common::getConverterServer() . '/api/v1/document/sign-text';
                $data = array(
                    "assign_id" => -1,
                    "document_id" => $id
                );
                $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
                Log::info($mergeResponse);
                if ($mergeResponse["status"] != true) throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
            }

            $doc = eCDocuments::find($id);

            $currentAssignee = $this->documentHandlingService->getNextAssignee($id, $user->company_id);

            if ($currentAssignee->assign_type == AssigneeType::SIGN && $currentAssignee->is_auto_sign == 1) {
                $doc->updated_by = $user->id;
                $doc->current_assignee_id = $currentAssignee->id;
                $doc->save();
                DB::commit();
                $this->signDocumentHandling($doc, $user, $currentAssignee, 2, $ip);
            } else {
                $password = Common::randomString(6);
                $urlCode = Common::randomString(10) . "-" . time();
                $sign_remote = [
                    "name" => $currentAssignee->full_name,
                    "email" => $currentAssignee->email,
                    "password" => $password,
                    "url_code" => $urlCode
                ];
                if ($currentAssignee->assign_type == 1 || $currentAssignee->assign_type == 2) {
                    eCDocumentAssignee::where('id', $currentAssignee->id)
                        ->update([
                            "password" => Hash::make($password),
                            "url_code" => $urlCode
                        ]);
                    if ($doc->parent_id != -1) {
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'CREATE_SIGN_REMOTE_ADDENDUM', $doc->code, json_encode($sign_remote));
                    } else {
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'CREATE_SIGN_REMOTE_DOCUMENT', $doc->code, json_encode($sign_remote));
                    }
                }
                $sendParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $currentAssignee->message);
                $this->documentHandlingService->sendNotifySign($currentAssignee, $doc, $sendParams);

                $doc->updated_by = $user->id;
                $doc->current_assignee_id = $currentAssignee->id;
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $currentAssignee->full_name, $user->name, $user->email);

                $doc->save();
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollback();
            if (str_contains($e, 'exists_code_unique')) {
                throw new eCBusinessException("SERVER.EXISTED_DOCUMENT_CODE");
            }
            throw $e;
        }
    }

    public function initViewDocument($id)
    {
        $user = Auth::user();
        $rejectReason = eCDocumentAssignee::join('ec_documents as d', 'ec_document_assignees.document_id', 'd.id')
                ->where('document_id', $id)
                ->where('d.document_state', 4)
                ->where('reason', '!=', NULL)
                ->select('reason')
                ->first();
        $document = eCDocuments::with(['assignee' => function($query) {
            $query->where('assign_type',AssigneeType::CREATOR);
        },'documentType'])->where('id', $id)
                ->where('company_id', $user->company_id)
                ->where('document_state', '!=', DocumentState::DRAFT)
                ->select('id', 'document_type_id', 'name', 'code', 'document_state', 'current_assignee_id', 'sent_date', 'expired_date', 'finished_date', 'created_at', 'is_verify_content', 'document_sample_id', 'branch_id', 'parent_id', 'is_order_approval', 'expired_type', 'doc_expired_date', 'expired_month', 'addendum_type')
                ->first();
        if (!$document) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        if ($user->is_personal) {
            if ($document->is_order_approval == 0) {
                $hasPermission = DB::select("SELECT * from ec_document_assignees a WHERE document_id = ? AND email = ? AND a.STATUS = 1 AND (a.state IN (1,2,3) OR id = ?)", array($id, $user->email, $document->current_assignee_id));
                if (count($hasPermission) == 0) {
                    throw new eCBusinessException("SERVER.NOT_PERMISSION_VIEW_DOCUMENT");
                }
            } else {
                $assignType = -1;
                if ($document->document_state == DocumentState::WAIT_APPROVAL) {
                    $assignType = AssigneeType::APPROVAL;
                } else if ($document->document_state == DocumentState::WAIT_SIGNING) {
                    $assignType = AssigneeType::SIGN;
                }
                $hasPermission = DB::select("SELECT * from ec_document_assignees a WHERE document_id = ? AND email = ? AND a.STATUS = 1 AND (a.state IN (1,2,3) OR a.assign_type = ?)", array($id, $user->email, $assignType));
                if (count($hasPermission) == 0) {
                    throw new eCBusinessException("SERVER.NOT_PERMISSION_VIEW_DOCUMENT");
                }
            }
        } else {
            if ($user->branch_id && $user->branch_id != -1) {
                if ($document->branch_id != $user->branch_id) {
                    $hasPerm = false;
                    if ($document->is_order_approval == 0) {
                        $hasPermission = DB::select("SELECT * from ec_document_assignees a WHERE document_id = ? AND email = ? AND a.STATUS = 1 AND (a.state IN (1,2,3) OR id = ?)", array($id, $user->email, $document->current_assignee_id));
                        if (count($hasPermission) > 0) {
                            $hasPerm = true;
                        }
                    } else {
                        $assignType = -1;
                        if ($document->document_state == DocumentState::WAIT_APPROVAL) {
                            $assignType = AssigneeType::APPROVAL;
                        } else if ($document->document_state == DocumentState::WAIT_SIGNING) {
                            $assignType = AssigneeType::SIGN;
                        }
                        $hasPermission = DB::select("SELECT * from ec_document_assignees a WHERE document_id = ? AND email = ? AND a.STATUS = 1 AND (a.state IN (1,2,3) OR a.assign_type = ?)", array($id, $user->email, $assignType));
                        if (count($hasPermission) > 0) {
                            $hasPerm = true;
                        }
                    }
                    if (!$hasPerm) {
                        throw new eCBusinessException("SERVER.NOT_PERMISSION_VIEW_DOCUMENT");
                    }
                }
            }
        }
        $document->partners = eCDocumentPartners::where('document_id', $id)
            ->where('status', 1)
            ->orderBy("order_assignee", "asc")->get();
        foreach ($document->partners as $partner) {
            $partner->assignees = eCDocumentAssignee::where('partner_id', $partner->id)
                ->whereIn('assign_type', [AssigneeType::APPROVAL, AssigneeType::SIGN, AssigneeType::STORAGE])
                ->where('status', 1)
                ->orderBy('assign_type', 'asc')
                ->get();
        }

        $company = null;
        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id) {
                $document->cur = eCDocumentAssignee::with('partner')
                    ->where('id', $document->current_assignee_id)
                    ->whereIn('state', [AssigneeState::NOT_RECEIVED, AssigneeState::RECEIVED])
                    ->first();
            }
        }
        $parent_doc = null;
        if ($document->parent_id != -1) {
            $parent_doc = eCDocuments::where('id', $document->parent_id)
                ->select('id', 'name')
                ->first();
            if ($document->document_state == DocumentState::WAIT_SIGNING) {
                if ($document->cur && $document->cur->email == $user->email) {
                    $company = eCCompanySignature::where('company_id', $user->company_id)->first();
                    $document->cur->signature = eCDocumentSignatureKyc::where('assign_id', $document->current_assignee_id)->first();
                    $document->signature_location = eCDocumentSignature::where('assign_id', $document->current_assignee_id)->get();
                }
            }
        } else {
            $assignee = $this->getCurrentAssigneeConcurrent($id, $document->document_state, $user);
            if ($assignee) {
                $document->cur = $assignee;
                if ($document->document_state == DocumentState::WAIT_SIGNING) {
                    $company = eCCompanySignature::where('company_id', $user->company_id)->first();
                    $document->cur->signature = eCDocumentSignatureKyc::where('assign_id', $assignee->id)->first();
                    $document->signature_location = eCDocumentSignature::where('assign_id', $assignee->id)->get();
                }
                $checkHsm = eCDocumentHsm::where('assignee_id', $assignee->id)->first();
                if($checkHsm) {
                    $document->issetHsm = $checkHsm;
                }
            }
        }
        $invalidDocument = false;
        $countSignature = 0;
        $dataSignature = null;
        try {
            // call api verify signature
            $url = Common::getConverterServer() . '/api/v1/read-signature';
            $data = array(
                "document_id" => $id
            );
            $response = $this->documentHandlingService->sendBackendServer($url, $data);
            if ($response["status"] = true) {
                // check response
                $data = json_decode($response['data']);

                if ($data->code == 400 && $data->message == "Invalid Document") $invalidDocument = true;
                // đếm số người đã ký HĐ == số chữ ký
                $countSignature = eCDocumentAssignee::where('document_id', $id)
                    ->where('assign_type', AssigneeType::SIGN)
                    ->where('state', AssigneeState::COMPLETED)
                    ->count();
                if ($data->code == 200) {
                    $dataSignature = $data->data;
                }
            }
            if ($rejectReason) {
                return array('document' => $document, 'company' => $company, 'reject_reason' => $rejectReason->reason, 'parent_doc' => $parent_doc, 'invalid_doc' => $invalidDocument, 'signature_data' => $dataSignature, 'had_signature' => $countSignature > 0 ? true : false, 'verify_signature' => true);
            }
            return array('document' => $document, 'company' => $company, 'parent_doc' => $parent_doc, 'invalid_doc' => $invalidDocument, 'signature_data' => $dataSignature, 'had_signature' => $countSignature > 0 ? true : false, 'verify_signature' => true);
        } catch (\Exception $e) {
            Log::error("Error processing verify_signature signature:");
            if ($rejectReason) {
                return array('document' => $document, 'company' => $company, 'reject_reason' => $rejectReason->reason, 'parent_doc' => $parent_doc, 'invalid_doc' => $invalidDocument, 'signature_data' => $dataSignature, 'had_signature' => $countSignature > 0 ? true : false, 'verify_signature' => false);
            }
            return array('document' => $document, 'company' => $company, 'parent_doc' => $parent_doc, 'invalid_doc' => $invalidDocument, 'signature_data' => $dataSignature, 'had_signature' => $countSignature > 0 ? true : false, 'verify_signature' => false);
        }
    }

    public function getCurrentAssigneeConcurrent($id, $documentState, $user)
    {
        $assignType = -1;
        if ($documentState == DocumentState::WAIT_APPROVAL) {
            $assignType = AssigneeType::APPROVAL;
        } else if ($documentState == DocumentState::WAIT_SIGNING) {
            $assignType = AssigneeType::SIGN;
        }
        return eCDocumentAssignee::with('partner')
            ->where('document_id', $id)
            ->where('email', $user->email)
            ->whereIn('state', [AssigneeState::NOT_RECEIVED, AssigneeState::RECEIVED])
            ->where('assign_type', $assignType)
            ->first();
    }

    public function selectSignature($assigneeId, $type, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) throw new eCAuthenticationException();

        $assignee = eCDocumentAssignee::where('id', $assigneeId)
            ->where('email', $user->email)
            ->where('status', 1)
            ->first();
        if (!$assignee) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");

        $document = eCDocuments::where('id', $assignee->document_id)
            ->where('company_id', $user->company_id)
            ->first();
        if (!$document)  throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id != $assigneeId) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        } else {
            if (
                !($assignee->assign_type == AssigneeType::APPROVAL && $document->document_state == DocumentState::WAIT_APPROVAL) &&
                !($assignee->assign_type == AssigneeType::SIGN && $document->document_state == DocumentState::WAIT_SIGNING)
            ) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        }

        if ($type == 0) {
            $signature = eCCompanySignature::where('company_id', $user->company_id)->first();
            if (!$signature) throw new eCBusinessException("SERVER.SIGNATURE_NOT_VALID");
        } else if ($type == 1) {
            $signature = eCUserSignature::where('user_id', $user->id)->first();
            if (!$signature) throw new eCBusinessException("SERVER.SIGNATURE_NOT_VALID");
        }

        $signatureData = $signature->image_signature;

        DB::beginTransaction();
        try {
            eCDocumentSignatureKyc::where('assign_id', $document->current_assignee_id)->delete();
            //TODO: luu anh chu ky tu plugin
            eCDocumentSignatureKyc::create([
                    "assign_id" => $document->current_assignee_id,
                    "image_signature" => $signatureData
                ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateSignatureLocation($assigneeId, $lstLocation, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) throw new eCAuthenticationException();

        $assignee = eCDocumentAssignee::where('id', $assigneeId)
            ->where('email', $user->email)
            ->where('status', 1)
            ->first();
        if (!$assignee) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");

        $document = eCDocuments::where('id', $assignee->document_id)
            ->where('company_id', $user->company_id)
            ->first();
        if (!$document) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id != $assigneeId) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        } else {
            if (
                !($assignee->assign_type == AssigneeType::APPROVAL && $document->document_state == DocumentState::WAIT_APPROVAL) &&
                !($assignee->assign_type == AssigneeType::SIGN && $document->document_state == DocumentState::WAIT_SIGNING)
            ) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        }
        try {
            eCDocumentSignature::where('assign_id', $assigneeId)->delete();
            $signatureDatas = array();
            foreach ($lstLocation as $signature) {
                array_push($signatureDatas, [
                    "document_id" => $document->id,
                    "assign_id" => $assigneeId,
                    "page_sign" => $signature["Page"],
                    "width_size" => $signature["Width"],
                    "height_size" => $signature["Height"],
                    "x" => $signature["XAxis"],
                    "y" => $signature["YAxis"],
                    "page_width" => $signature["pageWidth"],
                    "page_height" => $signature["pageHeight"],
                    "created_by" => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            eCDocumentSignature::insert($signatureDatas);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function sendOtp($id, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) throw new eCAuthenticationException();

        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_SIGNING) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }

        if (!$currentAssignee) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");

        $otpCode = Common::randomString(6, true);
        eCDocumentAssignee::where('id', $currentAssignee->id)
            ->update([
                'otp' => $otpCode
            ]);
        $sendOtpParams = $this->documentHandlingService->getExts($doc->id, "", "", "", "", "", "", "", $otpCode, "");
        $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $doc->id, NotificationType::SEND_OTP_DOCUMENT, $sendOtpParams);
        if ($doc->parent_id != -1) {
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'CREATE_OTP_ADDENDUM', $doc->code, json_encode($currentAssignee));
        } else {
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'CREATE_OTP_DOCUMENT', $doc->code, json_encode($currentAssignee));
        }
        return true;
    }

    public function verifyOcr($request, $document_group)
    {
        $user = Auth::user();
        $id = $request->docId;
        $type = $request->type;
        $image = $request->image;
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) throw new eCAuthenticationException();

        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_SIGNING) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }
        if (!$currentAssignee)  throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");

        $signature = eCUserSignature::where('user_id', $user->id)->first();
        if (!$signature) throw new eCBusinessException("DOCUMENT.NOT_SELECT_SIGNATURE");

        try {
            $assigneeSignature = eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->first();
            $upload_path = $document_group == DocumentType::INTERNAL ? '/internal/' : '/commerce/' ;
            $path = $this->storageHelper->uploadBase64File($image,  $upload_path . $id . '/');
            $postData = array();
            if ($type == 1) {
                $postData["front_image_url"] = $path;
            } else if ($type == 2) {
                $postData["back_image_url"] = $path;
            } else if ($type == 3) {
                $postData["face_image_url"] = $path;
            }
            if ($assigneeSignature) {
                eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->update($postData);
            } else {
                $postData["assign_id"] = $currentAssignee->id;
                $postData["image_signature"] = $signature->image_signature;
                eCDocumentSignatureKyc::create($postData);
            }
            $res = $this->documentHandlingService->sendOcrApi($currentAssignee->id, $type);
            return $res;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function approveDocument($id, $reason = "", $document_group,$ip)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_APPROVAL_MANAGE" : "COMMERCE_APPROVAL_MANAGE", false, false, true, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_APPROVAL) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }
        if (!$currentAssignee) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");

        $creator = eCUser::find($doc->created_by);

        DB::beginTransaction();
        try {
            $submitTime = date('Y-m-d H:i:s');
            $postData = [
                'id' => $currentAssignee->id,
                'state' => AssigneeState::COMPLETED, //da giao ket
                'reason' => $reason,
                'submit_time' => $submitTime
            ];
            eCDocumentAssignee::find($currentAssignee->id)->update($postData);
            $raw_log = $postData;
            $raw_log['assignees'] = $currentAssignee->full_name;
            $raw_log['assignees_email'] = $currentAssignee->email;

            //TODO: gui email thong bao da phe duyet

            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($id, $user->full_name, $submitTime, $reason, "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($id);
            if ($doc->parent_id != -1) {
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::AGREE_APPROVAL_ADDENDUM, $sendBeforeAssigneeParams);
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::AGREE_APPROVAL, "AGREE_APPROVAL_ADDENDUM", $currentAssignee->full_name, $currentAssignee->email);
            } else {
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::AGREE_APPROVAL_DOCUMENT, $sendBeforeAssigneeParams);
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::AGREE_APPROVAL, "AGREE_APPROVAL_DOCUMENT", $currentAssignee->full_name, $currentAssignee->email);
            }
            if ($doc->is_order_approval == 0) {
                $nextAssignee = $this->documentHandlingService->getNextAssignee($id, $user->company_id);
                if (!$nextAssignee) {
                    throw new eCBusinessException("SERVER.NOT_SIGNATURE_PARTICIPANT");
                } else {
                    $password = Common::randomString(6);
                    $urlCode = Common::randomString(10) . "-" . time();
                    if ($nextAssignee->assign_type == 1 || $nextAssignee->assign_type == 2) {
                        eCDocumentAssignee::where('id', $nextAssignee->id)
                            ->update([
                                "password" => Hash::make($password),
                                "url_code" => $urlCode
                            ]);
                    }
                    $sendNextAssigneeParams = $this->documentHandlingService->getExts($id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
                    if ($nextAssignee->assign_type == AssigneeType::SIGN && $nextAssignee->is_auto_sign) {
                        $doc->updated_by = $nextAssignee->id;
                        $doc->current_assignee_id = $nextAssignee->id;
                        $doc->document_state = DocumentState::WAIT_SIGNING;
                        $doc->save();
                        DB::commit();
                        $this->signDocumentHandling($doc, $user, $nextAssignee, 1, $ip);
                    } else {
                        $this->documentHandlingService->sendNotifySign($nextAssignee, $doc, $sendNextAssigneeParams);
                        $doc->current_assignee_id = $nextAssignee->id;
                        $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $nextAssignee->full_name, $doc->created_by == -1 ? "" : $creator->name, $doc->created_by == -1 ? "" : $creator->email);
                    }

                }
            } else {
                $assigneeElse = $this->documentHandlingService->getAllAssigneeByType($id, AssigneeType::APPROVAL);
                if (count($assigneeElse) == 0) {
                    $nextAssignees = $this->documentHandlingService->getAllAssigneeByType($id, AssigneeType::SIGN);
                    foreach ($nextAssignees as $assign) {
                        $password = Common::randomString(6);
                        $urlCode = Common::randomString(10) . "-" . time();
                        eCDocumentAssignee::where('id', $assign->id)
                            ->update([
                                "password" => Hash::make($password),
                                "url_code" => $urlCode
                            ]);
                        $sendParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $assign->message);

                        $this->documentHandlingService->sendNotifySign($assign, $doc, $sendParams);
                        $doc->document_state = DocumentState::WAIT_SIGNING;
                        $doc->updated_by = $user->id;
                        $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $assign->full_name, $user->name, $user->email);
                    }
                }
            }

            $doc->save();
            if ($doc->parent_id != -1) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'APPROVE_ADDENDUM', $doc->code, json_encode($raw_log));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'APPROVE_DOCUMENT', $doc->code, json_encode($raw_log));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function checkBeforeSign($id, $user, $document_group)
    {
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) throw new eCAuthenticationException();

        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_SIGNING) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }
        if (!$currentAssignee) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        return array('doc' => $doc, 'currentAssignee' => $currentAssignee);
    }
    public function signDocument($id, $ip, $document_group, $pubca, $ca)
    {
        $user = Auth::user();

        $data = $this->checkBeforeSign($id, $user, $document_group);
        $doc = $data['doc'];
        $currentAssignee = $data['currentAssignee'];
        $this->signDocumentHandling($doc, $user, $currentAssignee, 0, $ip, $ca, $pubca);
    }

    public function signOtpDocument($id, $ip, $otp, $document_group)
    {
        $user = Auth::user();
        $data =  $this->checkBeforeSign($id, $user, $document_group);
        $doc = $data['doc'];
        $currentAssignee = $data['currentAssignee'];

        if ($currentAssignee->otp != $otp) throw new eCBusinessException("SERVER.NOT_MATCH_OTP");

        $this->signDocumentHandling($doc, $user, $currentAssignee, 1, $ip);
    }

    public function signKycDocument($request, $document_group)
    {
        $user = Auth::user();
        $ip = $request->ip();
        $id = $request->docId;

        $data =  $this->checkBeforeSign($id, $user, $document_group);
        $doc = $data['doc'];
        $currentAssignee = $data['currentAssignee'];

        if ($currentAssignee->national_id != $request->id) throw new eCBusinessException("DOCUMENT.ERR_NOT_MATCH_NATIONAL_ID");

        if ($request->sim < 70) throw new eCBusinessException("DOCUMENT.ERR_NOT_MATCH_FACE");

        $postData = [
            'national_id' => $request->id,
            'name' => $request->name,
            'birthday' => $request->birthday,
            'sex' => $request->sex,
            'hometown' => $request->hometown,
            'address' => $request->address,
            'issueDate' => $request->issueDate,
            'issueBy' => $request->issueBy,
            'sim' => $request->sim,
        ];
        eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->update($postData);
        $this->signDocumentHandling($doc, $user, $currentAssignee, 2, $ip);
    }

    public function signMySignDocument($request, $document_group)
    {
        $user = Auth::user();
        $ip = $request->ip();
        $id = $request->docId;

        $data =  $this->checkBeforeSign($id, $user, $document_group);
        $doc = $data['doc'];
        $currentAssignee = $data['currentAssignee'];

        $postData = [
            'user_id' => $request->user_id,
            'credential_id' => $request->credential_id
        ];

        eCDocumentAssignee::where('id', $currentAssignee->id)->update($postData);

        $this->signDocumentHandling($doc, $user, $currentAssignee, 3, $ip);
    }

    function hashDoc($docId, $pubKey)
    {
        $user = Auth::user();
        $doc = eCDocuments::find($docId);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_SIGNING) {
            throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        }
        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($docId, $doc->document_state, $user);
        }
        $signature = eCUserSignature::where('user_id', $user->id)->first();
        if (!$signature) throw new eCBusinessException("DOCUMENT.NOT_SELECT_SIGNATURE");

        $assigneeSignature = eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->first();
        $postData = array(
            "image_signature" => $signature->image_signature
        );
        if ($assigneeSignature) {
            eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->update($postData);
        } else {
            $postData["assign_id"] = $currentAssignee->id;
            eCDocumentSignatureKyc::create($postData);
        }
        $url = Common::getConverterServer() . '/api/v1/document/hash';
        $data = array(
            "document_id" => $docId,
            "ca_pub" => $pubKey,
            "assign_id" => $currentAssignee->id
        );
        $response = $this->documentHandlingService->sendBackendServer($url, $data);
        Log::info($response);
        if ($response["status"] != true)  throw new eCBusinessException("SERVER.PROCESSING_ERROR");
        return $response;
    }

    function signDocumentHandling($doc, $user, $currentAssignee, $sign_type, $ip, $ca = "", $pubca = "")
    {
        $creator = eCUser::find($doc->created_by);

        if($currentAssignee->is_auto_sign){
            $signature = DB::select("SELECT us.image_signature FROM ec_user_signature us JOIN ec_users u ON u.id = us.user_id WHERE FROM_BASE64(u.email) = ? AND u.delete_flag = 0", array($currentAssignee->email));
        } else {
            $signature = eCUserSignature::where('user_id', $user->id)->get();
        }

        if (!$signature) throw new eCBusinessException("DOCUMENT.NOT_SELECT_SIGNATURE");

        $assigneeSignature = eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->first();
        $postData = array(
            "image_signature" => $signature[0]->image_signature
        );
        if ($assigneeSignature) {
            eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->update($postData);
        } else {
            $postData["assign_id"] = $currentAssignee->id;
            eCDocumentSignatureKyc::create($postData);
        }
        DB::beginTransaction();
        try {
            $submitTime = date('Y-m-d H:i:s');
            $pushData = [
                'id' => $currentAssignee->id,
                'state' => AssigneeState::COMPLETED, //da giao ket
                'submit_time' => $submitTime
            ];
            eCDocumentAssignee::where('id', $currentAssignee->id)->update($pushData);
            DB::commit();
            if ($doc->parent_id != -1) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'UPDATE_ASSIGNEE_ADDENDUM', $doc->code, json_encode($pushData));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'UPDATE_ASSIGNEE_DOCUMENT', $doc->code, json_encode($pushData));
            }
            $nextAssignee = $this->documentHandlingService->getNextAssignee($doc->id, $user->company_id);

            $url = Common::getConverterServer() . '/api/v1/document/sign';
            $data = array(
                "assign_id" => $currentAssignee->id,
                "document_id" => $doc->id,
                "sign_type" => $sign_type,
                "sign_action" => $nextAssignee ? 0 : 1,
                "ca" => $ca,
                "ca_pub" => $pubca,
                "sign_company" => $currentAssignee->partner->organisation_type == 3 ? 0 : 1
            );
            Log::info($data);
            if ($sign_type == 3) {
                $mergeResponse = $this->documentHandlingService->signMySign($url, $data, $ip);
            } else {
                $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            }
            Log::info($mergeResponse);
            if ($mergeResponse["status"] != true) {
                eCDocumentAssignee::where('id', $currentAssignee->id)
                    ->update([
                        'state' => AssigneeState::NOT_RECEIVED,
                        'submit_time' => null
                    ]);
                DB::commit();
                $mergeResponse = "SERVER.ERR_CONVERT_MERGE";
                if ($sign_type == 3) {
                    $mergeResponse = $this->documentHandlingService->throwMessage($mergeResponse["data"]);
                }
                throw new eCBusinessException($mergeResponse);
            }


            //TODO: gui email thong bao da phe duyet

            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($doc->id, $user->full_name, $submitTime, "", "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($doc->id);
            if ($doc->parent_id != -1) {
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $doc->id, NotificationType::AGREE_SIGN_ADDENDUM, $sendBeforeAssigneeParams);
            } else {
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $doc->id, NotificationType::AGREE_SIGN_DOCUMENT, $sendBeforeAssigneeParams);
            }

            //            $this->documentHandlingService->insertDocumentLog($doc->id, DocumentLogStatus::AGREE_APPROVAL, "Thực hiện ký số tài liệu thành công", $currentAssignee->full_name, $currentAssignee->email);

            $nextAssignee = $this->documentHandlingService->getNextAssignee($doc->id, $user->company_id);
            if (!$nextAssignee) {
                $doc->current_assignee_id = NULL;
                if ($doc->is_verify_content) {
                    //                    $doc->document_state = DocumentState::NOT_AUTHORIZE;
                } else {
                    $doc->document_state = DocumentState::COMPLETE;
                    $doc->finished_date = date('Y-m-d H:i:s');
                    $price = $this->documentHandlingService->getFeeTurnOver($doc);
                    $doc->price = $price ? $price : 1000;
                    if ($doc->expired_type == 2) {
                        $doc->doc_expired_date = date('Y-m-d H:i:s', strtotime('+' . $doc->expired_month . ' month', strtotime($doc->finished_date)));
                    }
                    if ($doc->parent_id != -1) {
                        if ($doc->addendum_type == AddendumType::SUPPLEMENT) {
                            $this->documentHandlingService->insertDocumentLog($doc->parent_id, DocumentLogStatus::AFTER_FINISH, "SUPPLEMENT_DOCUMENT", $user->name, $user->email);
                        }
                        if ($doc->addendum_type == AddendumType::RENEW) {
                            $parent_doc = eCDocuments::where('id', $doc->parent_id)->first();
                            if ($doc->expired_type == 0) {
                                eCDocuments::where('id', $doc->parent_id)
                                    ->update([
                                        'expired_type' => 0,
                                        'doc_expired_date' => null
                                    ]);
                            } else if ($doc->expired_type == 1) {
                                eCDocuments::where('id', $doc->parent_id)
                                    ->update([
                                        'expired_type' => 1,
                                        'doc_expired_date' => $doc->doc_expired_date
                                    ]);
                            } else if ($doc->expired_type == 2) {
                                eCDocuments::where('id', $doc->parent_id)
                                    ->update([
                                        'expired_type' => 1,
                                        'doc_expired_date' => date('Y-m-d H:i:s', strtotime('+' . $doc->expired_month . ' month', strtotime($parent_doc->doc_expired_date)))
                                    ]);
                            }
                            $this->documentHandlingService->insertDocumentLog($doc->parent_id, DocumentLogStatus::AFTER_FINISH, "RENEW_DOCUMENT", $user->name, $user->email);
                        }
                        if ($doc->addendum_type == AddendumType::DELETE) {
                            eCDocuments::where('id', $doc->parent_id)
                                ->update([
                                    'document_state' => DocumentState::DROP,
                                ]);
                            $this->documentHandlingService->insertDocumentLog($doc->parent_id, DocumentLogStatus::AFTER_FINISH, "DELETE_DOCUMENT", $user->name, $user->email);
                        }
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'COMPLETE_ADDENDUM', $doc->code, json_encode($doc));
                    } else {
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'COMPLETE_DOCUMENT', $doc->code, json_encode($doc));
                    }
                    DB::commit();
                }
            } else {
                if ($doc->is_order_approval == 0) {
                    if ($nextAssignee->assign_type == AssigneeType::SIGN && $nextAssignee->is_auto_sign == 1) {
                        $doc->current_assignee_id = $nextAssignee->id;
                        $doc->save();
                        DB::commit();
                        $this->signDocumentHandling($doc, $user, $nextAssignee, 2, $ip);
                    } else {
                        $password = Common::randomString(6);
                        $urlCode = Common::randomString(10) . "-" . time();
                        if ($nextAssignee->assign_type == 1 || $nextAssignee->assign_type == 2) {
                            eCDocumentAssignee::where('id', $nextAssignee->id)
                                ->update([
                                    "password" => Hash::make($password),
                                    "url_code" => $urlCode
                                ]);
                        }
                        $sendNextAssigneeParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
                        if ($nextAssignee->assign_type == AssigneeType::SIGN && $nextAssignee->is_auto_sign) {
                            $doc->updated_by = $nextAssignee->id;
                            $doc->current_assignee_id = $nextAssignee->id;
                            $doc->save();
                            DB::commit();
                            $this->signDocumentHandling($doc, $user, $nextAssignee, 1, $ip);
                        } else {
                            $this->documentHandlingService->sendNotifySign($nextAssignee, $doc, $sendNextAssigneeParams);
                            $doc->current_assignee_id = $nextAssignee->id;
                            $this->documentHandlingService->insertDocumentLog($doc->id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $nextAssignee->full_name, $doc->created_by == -1 ? "" : $creator->name, $doc->created_by == -1 ? "" : $creator->email);
                        }

                    }
                }
            }

            $doc->save();
            $raw_log = [
                "assignee" => $currentAssignee->full_name,
                "assignee_email" => $currentAssignee->email,
                "message" => $currentAssignee->message,
                "doc" => $doc,
            ];
            if ($doc->parent_id != -1) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'SIGN_ADDENDUM', $doc->code, json_encode($raw_log));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'SIGN_DOCUMENT', $doc->code, json_encode($raw_log));
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function denyDocument($id, $rejectContent, $document_group)
    {
        $user = Auth::user();
        $doc = eCDocuments::find($id);
        if ($doc->document_state == DocumentState::WAIT_APPROVAL) {
            $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_APPROVAL_MANAGE" : "COMMERCE_APPROVAL_MANAGE", false, false, true, false);
            if (!$hasPermission) throw new eCAuthenticationException();
        } else if ($doc->document_state == DocumentState::WAIT_SIGNING) {
            $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
            if (!$hasPermission) throw new eCAuthenticationException();
        }
        if (!$doc || $doc->company_id != $user->company_id || ($doc->document_state != DocumentState::WAIT_APPROVAL && ($doc->document_state != DocumentState::WAIT_SIGNING))) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }
        if (!$currentAssignee) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");

        DB::beginTransaction();
        try {
            $submitTime = date('Y-m-d H:i:s');
            eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->update([
                    'state' => AssigneeState::REJECT, //da tu choi
                    'reason' => $rejectContent,
                    'submit_time' => $submitTime
                ]);
            $raw_log = [
                "assignee" => $currentAssignee->full_name,
                "assignee_email" => $currentAssignee->email,
                'reason' => $rejectContent,
                'submit_time' => $submitTime,
                "doc" => $doc,
            ];
            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($id, $user->full_name, $submitTime, $rejectContent, "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($id);
            if ($doc->parent_id != -1) {
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::DENY_ADDENDUM, $sendBeforeAssigneeParams);
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::DENY_APPROVAL, "DENY_APPROVAL_ADDENDUM/" . $rejectContent, $currentAssignee->full_name, $currentAssignee->email);
            } else {
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::DENY_DOCUMENT, $sendBeforeAssigneeParams);
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::DENY_APPROVAL, "DENY_APPROVAL_DOCUMENT/" . $rejectContent, $currentAssignee->full_name, $currentAssignee->email);
            }

            $doc->document_state = DocumentState::DENY;
            $doc->current_assignee_id = NULL;

            $doc->save();
            if ($doc->parent_id != -1) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'DENY_ADDENDUM', $doc->code, json_encode($raw_log));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'DENY_DOCUMENT', $doc->code, json_encode($raw_log));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function editDenyDocument($id, $document_group)
    {
        $user = Auth::user();
        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::DENY) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        if ($doc->created_by != $user->id || $doc->document_sample_id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        DB::beginTransaction();
        try {

            $doc->document_state = DocumentState::DRAFT;
            $doc->document_draft_state = 1;

            $lstPartners = eCDocumentPartners::where('document_id', $id)->where('status', 1)->get();
            eCDocumentAssignee::where('document_id', $id)
                ->where('assign_type', '!=', 0)
                ->update([
                    'status' => 0
                ]);
            eCDocumentPartners::where('document_id', $id)
                ->update([
                    'status' => 0
                ]);
            eCDocumentResources::where('document_id', $id)
                ->update([
                    'status' => 0
                ]);
            eCDocumentResourcesEx::where('document_id', $id)
                ->update([
                    'status' => 0
                ]);
            eCDocumentSignature::where('document_id', $id)->delete();
            eCDocumentTextInfo::where('document_id', $id)->delete();
            foreach ($lstPartners as $partner) {
                $partnerNew = eCDocumentPartners::create([
                    "document_id" => $id,
                    "order_assignee" => $partner->order_assignee,
                    "organisation_type" => $partner->organisation_type,
                    "company_name" => $partner->company_name,
                    "tax" => $partner->tax,
                    "created_by" => $user->id
                ]);
                $lstAssignees = eCDocumentAssignee::where('partner_id', $partner->id)->get();
                $assigneeData = array();
                foreach ($lstAssignees as $assignee) {
                    array_push($assigneeData, [
                        "company_id" => $assignee->company_id,
                        "full_name" => $assignee->full_name,
                        "email" => $assignee->email,
                        "phone" => $assignee->phone,
                        "document_id" => $id,
                        "partner_id" => $partner->id,
                        "message" => $assignee->message,
                        "noti_type" => $assignee->noti_type,
                        "assign_type" => $assignee->assign_type,
                        "created_by" => $user->id
                    ]);
                }
                $partnerNew->assignee()->createMany($assigneeData);
            }
            $doc->save();
            if ($doc->parent_id != -1) {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "EDIT_ADDENDUM_DENY", $user->name, $user->email);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_partners', 'UPDATE_ASSIGNEE_ADDENDUM_DENY', $doc->code, json_encode($partner));
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'UPDATE_DENY_ADDENDUM', $doc->code, json_encode($doc));
            } else {
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::EDIT_DOCUMENT, "EDIT_DOCUMENT_DENY", $user->name, $user->email);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_partners', 'UPDATE_ASSIGNEE_DOCUMENT_DENY', $doc->code, json_encode($partner));
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'UPDATE_DENY_DOCUMENT', $doc->code, json_encode($doc));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initDocumentListSetting($document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_DOCUMENT_LIST" : "COMMERCE_DOCUMENT_LIST");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', $document_group)
            ->get();
        $lstCreator = eCUser::where('company_id', $user->company_id)
            ->where('status',1)
            ->select('id', 'name')
            ->get();
        return array('lstDocumentType' => $lstDocumentType, 'permission' => $permission, 'lstCreator' => $lstCreator);
    }

    public function searchDocumentList($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_DOCUMENT_LIST" : "COMMERCE_DOCUMENT_LIST", true, false, false, false);
        if (!$hasPermission)  throw new eCAuthenticationException();
        $arr = array();
        $str = 'SELECT distinct(d.id), d.company_id, d.document_type_id, d.document_state, d.document_draft_state, d.doc_expired_date, d.addendum_type, d.status, d.sent_date, d.expired_date, d.finished_date, d.name, d.code, d.created_by, d.is_verify_content , dt.dc_style, d.branch_id FROM ec_documents d ';
        $strCount = 'SELECT count(*) as cnt FROM ec_documents d';
        if ($user->is_personal) {
            $str .= ' JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND a.status = 1 AND (a.state in (0,1,2,3) OR d.current_assignee_id = a.id)';
            $strCount .= ' JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND a.status = 1 AND (a.state in (0,1,2,3) OR d.current_assignee_id = a.id)';
            array_push($arr, $user->email);
        } else {
            if ($user->branch_id && $user->branch_id != -1) {
                $str .= ' JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND a.status = 1 AND ((a.state in (0,1,2,3) OR d.current_assignee_id = a.id) OR d.branch_id = ?) ';
                $strCount .= ' JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND a.status = 1 AND ((a.state in (0,1,2,3) OR d.current_assignee_id = a.id) OR d.branch_id = ?) ';
                array_push($arr, $user->email);
                array_push($arr, $user->branch_id);
            }
        }

        $str .= ' JOIN s_document_types dt ON d.document_type_id = dt.id';
        $strCount .= ' JOIN s_document_types dt ON d.document_type_id = dt.id';
        $str .= " WHERE d.company_id = ? AND d.delete_flag = 0 and d.document_type = ? ";
        $strCount .= " WHERE d.company_id = ? AND d.delete_flag = 0 and d.document_type = ? ";
        array_push($arr, $user->company_id);
        array_push($arr, $document_group);

        if ($searchData['parent_id']) {
            $str .= ' AND d.parent_id = ?';
            $strCount .= ' AND d.parent_id = ?';
            array_push($arr, $searchData["parent_id"]);
        }

        if ($searchData["addendum_type"] != -1) {
            $str .= ' AND d.addendum_type = ?';
            $strCount .= ' AND d.addendum_type = ?';
            array_push($arr, $searchData["addendum_type"]);
        }
        if ($searchData["dc_style"] != "-1") {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            $strCount .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }
        if ($searchData["creator_id"] != -1) {
            $str .= ' AND d.created_by = ?';
            $strCount .= ' AND d.created_by = ?';
            array_push($arr, $searchData["creator_id"]);
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND d.document_type_id = ?';
            $strCount .= ' AND d.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["document_state"] != -1) {
            $str .= ' AND d.document_state = ?';
            $strCount .= ' AND d.document_state = ?';
            array_push($arr, $searchData["document_state"]);
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
        foreach ($res as $r) {
            $r->addendum = [];
            $r->addendum = DB::select("SELECT a1.* FROM ec_documents a1 JOIN ec_documents a2 ON a1.parent_id = a2.id WHERE a2.id = ? AND a2.delete_flag = 0 ", array($r->id));
        }

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function deleteDocumentListSetting($ids = [], $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_DOCUMENT_LIST" : "COMMERCE_DOCUMENT_LIST", false, true, false, false);
        if (!$hasPermission)  throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_documentList = eCDocuments::select('id', 'name', 'parent_id')
            ->whereIn('id', $ids)->whereIn('document_state', [DocumentState::DRAFT, DocumentState::DENY, DocumentState::OVERDUE, DocumentState::DROP])
            ->where('company_id', $company_id)
            ->get();
        $i = 1;
        $is_addendum = false;
        foreach ($avail_documentList as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->name;
            if ($p->parent_id != -1) {
                $is_addendum = true;
            }
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('INTERNAL.DOCUMENT_LIST.NOT_EXISTED_DOCUMENT');

        try {
            DB::beginTransaction();
            eCDocuments::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            if ($is_addendum) {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'DELETE_ADDENDUM', $count, json_encode($avail_rm));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_documents', 'DELETE_DOCUMENT', $count, json_encode($avail_rm));
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initSignManageSetting($document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', $document_group)
            ->get();
        return array('lstDocumentType' => $lstDocumentType, 'permission' => $permission);
    }

    public function searchSignManage($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT distinct d.id, d.* FROM (SELECT ed.id, ed.company_id, ed.sent_date, ed.name, ed.branch_id, ed.code, ed.parent_id , ed.addendum_type, dt.dc_style, FROM_BASE64(eu.name) as creator_name, SUM(case when p.organisation_type = 1 then 1 ELSE 0 END) AS count_1, SUM(case when p.organisation_type != 1 then 1 ELSE 0 END) AS count_2, a2.email FROM ec_documents as ed JOIN ec_users as eu ON ed.created_by = eu.id JOIN ec_document_partners p ON ed.id = p.document_id JOIN ec_document_assignees a ON p.id = a.partner_id LEFT JOIN ec_document_assignees a2 ON ed.current_assignee_id = a2.id JOIN s_document_types dt ON ed.document_type_id = dt.id WHERE ed.company_id = ? AND ed.document_type = ? AND ed.delete_flag = 0 AND document_state = ? AND a.assign_type = ? AND a.`status` = 1 ';
        $strCount = 'SELECT count(*) as cnt FROM (SELECT ed.id, ed.company_id, ed.sent_date, ed.name, ed.branch_id, ed.code, ed.parent_id , ed.addendum_type, FROM_BASE64(eu.name) as creator_name, SUM(case when p.organisation_type = 1 then 1 ELSE 0 END) AS count_1, SUM(case when p.organisation_type != 1 then 1 ELSE 0 END) AS count_2, a2.email FROM ec_documents as ed JOIN ec_users as eu ON ed.created_by = eu.id JOIN ec_document_partners p ON ed.id = p.document_id JOIN ec_document_assignees a ON p.id = a.partner_id LEFT JOIN ec_document_assignees a2 ON ed.current_assignee_id = a2.id JOIN s_document_types dt ON ed.document_type_id = dt.id WHERE ed.company_id = ? AND ed.document_type = ? AND ed.delete_flag = 0 AND document_state = ? AND a.assign_type = ? AND a.`status` = 1 ';
        $arr = array($user->company_id, $document_group, DocumentState::WAIT_SIGNING, AssigneeType::SIGN);

        if ($searchData['parent_id'] == -1) {
            $str .= ' AND ed.parent_id = ?';
            $strCount .= ' AND ed.parent_id = ?';
            array_push($arr, $searchData["parent_id"]);
        } else {
            $str .= ' AND ed.parent_id != -1';
            $strCount .= ' AND ed.parent_id != -1';
        }

        if ($searchData['addendum_type'] != -1) {
            $str .= ' AND ed.addendum_type = ?';
            $strCount .= ' AND ed.addendum_type = ?';
            array_push($arr, $searchData["addendum_type"]);
        }

        if ($searchData["dc_style"] != "-1") {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }
        if (!empty($searchData["keyword"])) {
            $str .= ' AND (ed.name LIKE ? OR ed.code LIKE ?)';
            $strCount .= ' AND (ed.name LIKE ? OR ed.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["sign_method"] != -1) {
            $str .= ' AND a.sign_method LIKE ?';
            $strCount .= ' AND a.sign_method LIKE ?';
            $sign_method = '%' . $searchData["sign_method"] . '%';
            array_push($arr, $sign_method);
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND ed.document_type_id = ?';
            $strCount .= ' AND ed.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? ';
            $strCount .= ' AND ed.sent_date >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        if ($searchData['creator_type_id'] == 0) {
            $str .= ' AND a2.email = ? ';
            $strCount .= ' AND a2.email = ? ';
            array_push($arr, $user->email);
        } else if ($searchData['creator_type_id'] == 1) {
            $str .= ' AND a2.email != ? ';
            $strCount .= ' AND a2.email != ? ';
            array_push($arr, $user->email);
        }

        $str .= " GROUP BY ed.id ORDER BY " . $sortQuery . ") d ";
        $strCount .= ' GROUP BY ed.id) d ';

        if ($user->is_personal) {
            $str .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND da.email= ? AND da.status = 1 ';
            $strCount .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND da.email= ? AND da.status = 1 ';
            array_push($arr, $user->email);
        } else {
            if ($user->branch_id && $user->branch_id != -1) {
                $str .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND (da.email = ? OR d.branch_id = ?) AND da.status = 1 ';
                $strCount .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND (da.email= ? OR d.branch_id = ?) AND da.status = 1 ';
                array_push($arr, $user->email);
                array_push($arr, $user->branch_id);
            }
        }

        $str .= " WHERE 1=1 ";
        $strCount .= " WHERE 1=1 ";
        if ($searchData['consignee_type_id'] == 0) {
            $str .= " AND count_1 > 0";
            $strCount .= " AND count_1 > 0";
        } else if ($searchData['consignee_type_id'] == 1) {
            $str .= " AND count_2 > 0";
            $strCount .= " AND count_2 > 0";
        }

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        foreach ($res as $r) {
            $r->partner = [];
            $r->myOrganisation = [];
            if ($r->parent_id != -1) {
                $r->parent_doc = eCDocuments::where('id', $r->parent_id)
                    ->select('id', 'name')
                    ->first();
            }
            if ($r->count_1 > 0) {
                $r->myOrganisation = DB::select("SELECT a.full_name as name, a.email, a.state, a.national_id, a.sign_method FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND p.organisation_type = 1 AND a.assign_type = ? and a.status = 1", array($r->id, AssigneeType::SIGN));
            }
            if ($r->count_2 > 0) {
                $r->partner = DB::select("SELECT a.full_name as name, a.email, a.state, a.national_id, a.sign_method FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND p.organisation_type != 1 AND a.assign_type = ? and a.status = 1", array($r->id, AssigneeType::SIGN));
            }
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function initApprovalManageSetting($document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_APPROVAL_MANAGE" : "COMMERCE_APPROVAL_MANAGE");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', $document_group)
            ->get();
        return array('lstDocumentType' => $lstDocumentType, 'permission' => $permission);
    }

    public function searchApprovalManage($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_APPROVAL_MANAGE" : "COMMERCE_APPROVAL_MANAGE", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $str = 'SELECT distinct d.id, d.* FROM (SELECT ed.id, ed.company_id, ed.sent_date, ed.name, ed.code, ed.parent_id, ed.addendum_type, dt.dc_style, ed.branch_id, FROM_BASE64(eu.name) as creator_name, SUM(case when p.organisation_type = 1 then 1 ELSE 0 END) AS count_1, SUM(case when p.organisation_type != 1 then 1 ELSE 0 END) AS count_2, a2.email FROM ec_documents as ed JOIN ec_users as eu ON ed.created_by = eu.id JOIN ec_document_partners p ON ed.id = p.document_id JOIN ec_document_assignees a ON p.id = a.partner_id LEFT JOIN ec_document_assignees a2 ON ed.current_assignee_id = a2.id JOIN s_document_types dt ON ed.document_type_id = dt.id WHERE ed.company_id = ? AND ed.document_type = ? AND ed.delete_flag = 0 AND document_state = ? AND a.assign_type = ? AND a.`status` = 1 ';
        $strCount = 'SELECT count(*) as cnt FROM (SELECT ed.id, ed.company_id, ed.sent_date, ed.name, ed.code, ed.parent_id, ed.addendum_type, ed.branch_id, FROM_BASE64(eu.name) as creator_name, SUM(case when p.organisation_type = 1 then 1 ELSE 0 END) AS count_1, SUM(case when p.organisation_type != 1 then 1 ELSE 0 END) AS count_2, a2.email FROM ec_documents as ed JOIN ec_users as eu ON ed.created_by = eu.id JOIN ec_document_partners p ON ed.id = p.document_id JOIN ec_document_assignees a ON p.id = a.partner_id LEFT JOIN ec_document_assignees a2 ON ed.current_assignee_id = a2.id JOIN s_document_types dt ON ed.document_type_id = dt.id WHERE ed.company_id = ? AND ed.document_type = ? AND ed.delete_flag = 0 AND document_state = ? AND a.assign_type = ? AND a.`status` = 1 ';
        $arr = array($user->company_id, $document_group, DocumentState::WAIT_APPROVAL, AssigneeType::APPROVAL);

        if ($searchData['parent_id'] == -1) {
            $str .= ' AND ed.parent_id = ?';
            $strCount .= ' AND ed.parent_id = ?';
            array_push($arr, $searchData["parent_id"]);
        } else {
            $str .= ' AND ed.parent_id != -1';
            $strCount .= ' AND ed.parent_id != -1';
        }

        if ($searchData['addendum_type'] != -1) {
            $str .= ' AND ed.addendum_type = ?';
            $strCount .= ' AND ed.addendum_type = ?';
            array_push($arr, $searchData["addendum_type"]);
        }

        if ($searchData["dc_style"] != "-1") {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }
        if (!empty($searchData["keyword"])) {
            $str .= ' AND (ed.name LIKE ? OR ed.code LIKE ?)';
            $strCount .= ' AND (ed.name LIKE ? OR ed.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND ed.document_type_id = ?';
            $strCount .= ' AND ed.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? ';
            $strCount .= ' AND ed.sent_date >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        if ($searchData['creator_type_id'] == 0) {
            $str .= ' AND a2.email = ? ';
            $strCount .= ' AND a2.email = ? ';
            array_push($arr, $user->email);
        } else if ($searchData['creator_type_id'] == 1) {
            $str .= ' AND a2.email != ? ';
            $strCount .= ' AND a2.email != ? ';
            array_push($arr, $user->email);
        }

        $str .= " GROUP BY ed.id ORDER BY " . $sortQuery . ") d ";
        $strCount .= ' GROUP BY ed.id) d ';

        if ($user->is_personal) {
            $str .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND da.email= ? AND da.status = 1 ';
            $strCount .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND da.email= ? AND da.status = 1 ';
            array_push($arr, $user->email);
        } else {
            if ($user->branch_id && $user->branch_id != -1) {
                $str .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND (da.email = ? OR d.branch_id = ?) AND da.status = 1 ';
                $strCount .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND (da.email= ? OR d.branch_id = ?) AND da.status = 1 ';
                array_push($arr, $user->email);
                array_push($arr, $user->branch_id);
            }
        }

        $str .= " WHERE 1=1 ";
        $strCount .= " WHERE 1=1 ";
        if ($searchData['consignee_type_id'] == 0) {
            $str .= " AND count_1 > 0";
            $strCount .= " AND count_1 > 0";
        } else if ($searchData['consignee_type_id'] == 1) {
            $str .= " AND count_2 > 0";
            $strCount .= " AND count_2 > 0";
        }

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        foreach ($res as $r) {
            $r->partner = [];
            $r->myOrganisation = [];
            if ($r->parent_id != -1) {
                $r->parent_doc = eCDocuments::where('id', $r->parent_id)
                    ->select('id', 'name')
                    ->first();
            }
            if ($r->count_1 > 0) {
                $r->myOrganisation = DB::select("SELECT a.full_name as name, a.email, a.state FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND p.organisation_type = 1 AND a.assign_type = ? and a.status = 1", array($r->id, AssigneeType::APPROVAL));
            }
            if ($r->count_2 > 0) {
                $r->partner = DB::select("SELECT a.full_name as name, a.email, a.state FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND p.organisation_type != 1 AND a.assign_type = ? and a.status = 1", array($r->id, AssigneeType::APPROVAL));
            }
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function initSendEmailSetting($document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_EMAIL" : "COMMERCE_SEND_EMAIL");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', $document_group)
            ->get();
        return array('lstDocumentType' => $lstDocumentType, 'permission' => $permission);
    }

    public function searchSendEmail($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_EMAIL" : "COMMERCE_SEND_EMAIL", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        
        //truy vấn dữ liệu
        $str = 'SELECT c.id, c.created_at, c.content, FROM_BASE64(u.name) AS sender_name, a.full_name AS receiver_name, a.email AS receiver_email, d.sent_date, d.code, d.name, d.id as doc_id, c.`status` FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types dt ON d.document_type_id = dt.id WHERE d.company_id = ? AND c.delete_flag = 0 AND c.send_type = 1 AND d.document_type = ? ';
        
        //đếm số lượng bản ghi tương ứng.
        $strCount = 'SELECT count(*) as cnt FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types dt ON d.document_type_id = dt.id WHERE d.company_id = ?  AND c.send_type = 1 AND c.delete_flag = 0 AND d.document_type = ? ';
        $arr = array($user->company_id, $document_group);


        if ($searchData["dc_style"] != "-1") {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }
        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ? OR a.email LIKE ? )';
            $strCount .= ' AND (d.name LIKE ? OR d.code LIKE ? OR a.email LIKE ? )';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND d.document_type_id = ?';
            $strCount .= ' AND d.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND c.status = ?';
            $strCount .= ' AND c.status = ?';
            array_push($arr, $searchData["status"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND c.created_at >= ? AND c.created_at <= ?';
            $strCount .= ' AND c.created_at >= ? AND c.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND c.created_at >= ? ';
            $strCount .= ' AND c.created_at >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND c.created_at <= ?';
            $strCount .= ' AND c.created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        Log::info($str);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function deleteSendEmailSetting($ids = [], $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_EMAIL" : "COMMERCE_SEND_EMAIL", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $avail_email = ecDocumentConversations::select('id', 'content')
            ->whereIn('id', $ids)->where('company_id', $user->company_id)->get();
        $i = 1;
        Log::info($avail_email);
        foreach ($avail_email as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->content;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('INTERNAL.SEND_EMAIL.ERR_NEED_CHOOSE_SEND_EMAIL');

        try {
            DB::beginTransaction();
            ecDocumentConversations::whereIn('id', $avail_ids)->update([
                'delete_flag' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_conversations', 'DELETE_MAIL', $count, json_encode($avail_rm));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function sendEmailSetting($ids = [], $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_EMAIL" : "COMMERCE_SEND_EMAIL", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $avail_email = ecDocumentConversations::select('id', 'content')
            ->whereIn('id', $ids)->where('company_id', $user->company_id)->get();
        $i = 1;
        foreach ($avail_email as $p) {
            $avail_ids[] = $p->id;
            $avail_rs[$i++] = $p->content;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('INTERNAL.SEND_EMAIL.ERR_NEED_CHOOSE_SEND_EMAIL');

        try {
            DB::beginTransaction();
            foreach ($avail_ids as $id) {
                $this->documentHandlingService->resendNotification($id);
            }
            DB::commit();
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_conversations', 'RESEND_MAIL', $count, json_encode($avail_rs));
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initSendSmsSetting($document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_SMS" : "COMMERCE_SEND_SMS");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', $document_group)
            ->get();
        return array('lstDocumentType' => $lstDocumentType, 'permission' => $permission);
    }

    public function searchSendSms($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_SMS" : "COMMERCE_SEND_SMS", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT c.id, c.created_at, c.content, FROM_BASE64(u.name) AS sender_name, a.full_name AS receiver_name, a.phone AS receiver_phone, d.sent_date, d.code, d.name, d.id as doc_id, c.`status` FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types dt ON d.document_type_id = dt.id WHERE d.company_id = ?  AND c.send_type = 0 AND c.delete_flag = 0 AND  d.document_type = ? ';
        $strCount = 'SELECT count(*) as cnt FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types dt ON d.document_type_id = dt.id WHERE d.company_id = ? AND c.delete_flag = 0  AND c.send_type = 0 AND d.document_type = ? ';

        $arr = array($user->company_id, $document_group);

        if ($searchData["dc_style"] != "-1") {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }
        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ? OR a.phone LIKE ?)';
            $strCount .= ' AND (d.name LIKE ? OR d.code LIKE ? OR a.phone LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND d.document_type_id = ?';
            $strCount .= ' AND d.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["status"] != -1) {
            $str .= ' AND c.status = ?';
            $strCount .= ' AND c.status = ?';
            array_push($arr, $searchData["status"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND c.created_at >= ? AND c.created_at <= ?';
            $strCount .= ' AND c.created_at >= ? AND c.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND c.created_at >= ? ';
            $strCount .= ' AND c.created_at >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND c.created_at <= ?';
            $strCount .= ' AND c.created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function deleteSendSmsSetting($ids = [], $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_SMS" : "COMMERCE_SEND_SMS", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $avail_email = ecDocumentConversations::select('id', 'content')
            ->whereIn('id', $ids)->where('company_id', $user->company_id)->get();
        $i = 1;
        foreach ($avail_email as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = $p->content;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('INTERNAL.SEND_EMAIL.ERR_NEED_CHOOSE_SEND_SMS');

        try {
            DB::beginTransaction();
            ecDocumentConversations::whereIn('id', $avail_ids)->update([
                'delete_flag' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_conversations', 'DELETE_SMS', $count, json_encode($avail_rm));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function sendSmsSetting($ids = [], $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SEND_SMS" : "COMMERCE_SEND_SMS", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $avail_email = ecDocumentConversations::select('id', 'content')
            ->whereIn('id', $ids)->where('company_id', $user->company_id)->get();
        $i = 1;
        foreach ($avail_email as $p) {
            $avail_ids[] = $p->id;
            $avail_rs[$i++] = $p->content;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('INTERNAL.SEND_EMAIL.ERR_NEED_CHOOSE_SEND_SMS');

        try {
            DB::beginTransaction();
            foreach ($avail_ids as $id) {
                $this->documentHandlingService->resendNotification($id);
            }
            DB::commit();
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_conversations', 'RESEND_SMS', $count, json_encode($avail_rs));
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function removeFile($file_id)
    {
        $user = Auth::user();
        try {
            if ($file_id != null || $file_id != "") {
                eCDocumentResources::where('file_id', $file_id)->delete();
            } else {
                throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function initAddendumManage($document_group)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE");
        if (!$permission || $permission->is_view != 1)  throw new eCAuthenticationException();

        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', $document_group)
            ->get();
        return array('lstDocumentType' => $lstDocumentType, 'permission' => $permission);
    }

    public function searchAddendumManage($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $str = 'SELECT distinct d.id, d.* FROM (SELECT ed.id, ed.company_id, ed.sent_date, ed.document_state, ed.parent_id, ed.addendum_type, ed.name, ed.branch_id, ed.code, dt.dc_style, FROM_BASE64(eu.name) as creator_name, SUM(case when p.organisation_type = 1 then 1 ELSE 0 END) AS count_1, SUM(case when p.organisation_type != 1 then 1 ELSE 0 END) AS count_2, a2.email FROM ec_documents as ed JOIN ec_users as eu ON ed.created_by = eu.id JOIN ec_document_partners p ON ed.id = p.document_id JOIN ec_document_assignees a ON p.id = a.partner_id LEFT JOIN ec_document_assignees a2 ON ed.current_assignee_id = a2.id JOIN s_document_types dt ON ed.document_type_id = dt.id WHERE ed.company_id = ? AND ed.document_type = ? AND ed.delete_flag = 0 AND a.`status` = 1 AND ed.parent_id != -1 ';
        $strCount = 'SELECT count(*) as cnt FROM (SELECT ed.id, ed.company_id, ed.sent_date, ed.document_state, ed.parent_id, ed.addendum_type, ed.name, ed.branch_id, ed.code, dt.dc_style, FROM_BASE64(eu.name) as creator_name, SUM(case when p.organisation_type = 1 then 1 ELSE 0 END) AS count_1, SUM(case when p.organisation_type != 1 then 1 ELSE 0 END) AS count_2, a2.email FROM ec_documents as ed JOIN ec_users as eu ON ed.created_by = eu.id JOIN ec_document_partners p ON ed.id = p.document_id JOIN ec_document_assignees a ON p.id = a.partner_id LEFT JOIN ec_document_assignees a2 ON ed.current_assignee_id = a2.id JOIN s_document_types dt ON ed.document_type_id = dt.id WHERE ed.company_id = ? AND ed.document_type = ? AND ed.delete_flag = 0 AND a.`status` = 1 AND ed.parent_id != -1 ';
        $arr = array($user->company_id, $document_group);

        if (!$user->is_personal) {
            if ($user->branch_id && $user->branch_id != -1) {
                $str .= ' AND ed.branch_id = ?';
                $strCount .= ' AND ed.branch_id = ?';
                array_push($arr, $user->branch_id);
            }
        }
        if ($searchData['document_state']) {
            $document_state = $searchData['document_state'] == DocumentState::WAIT_APPROVAL ? DocumentState::WAIT_APPROVAL : DocumentState::WAIT_SIGNING;
            $assign_type = $searchData['document_state'] == DocumentState::WAIT_APPROVAL ? AssigneeType::APPROVAL : AssigneeType::SIGN;
            $str .= ' AND ed.document_state = ? AND a.assign_type = ?';
            $strCount .= ' AND ed.document_state = ? AND a.assign_type = ?';
            array_push($arr, $document_state);
            array_push($arr, $assign_type);
        }


        if ($searchData["dc_style"] != "-1") {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }
        if (!empty($searchData["keyword"])) {
            $str .= ' AND (ed.name LIKE ? OR ed.code LIKE ?)';
            $strCount .= ' AND (ed.name LIKE ? OR ed.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["sign_method"] != -1) {
            $str .= ' AND a.sign_method LIKE ?';
            $strCount .= ' AND a.sign_method LIKE ?';
            $sign_method = '%' . $searchData["sign_method"] . '%';
            array_push($arr, $sign_method);
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND ed.document_type_id = ?';
            $strCount .= ' AND ed.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? ';
            $strCount .= ' AND ed.sent_date >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        if ($searchData['creator_type_id'] == 0) {
            $str .= ' AND a2.email = ? ';
            $strCount .= ' AND a2.email = ? ';
            array_push($arr, $user->email);
        } else if ($searchData['creator_type_id'] == 1) {
            $str .= ' AND a2.email != ? ';
            $strCount .= ' AND a2.email != ? ';
            array_push($arr, $user->email);
        }

        $str .= " GROUP BY ed.id ORDER BY " . $sortQuery . ") d ";
        $strCount .= ' GROUP BY ed.id) d ';

        if ($user->is_personal) {
            $str .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND da.email= ? AND da.status = 1 ';
            $strCount .= ' JOIN ec_document_assignees da ON d.id = da.document_id AND da.email= ? AND da.status = 1 ';
            array_push($arr, $user->email);
        }

        $str .= " WHERE 1=1 ";
        $strCount .= " WHERE 1=1 ";
        if ($searchData['consignee_type_id'] == 0) {
            $str .= " AND count_1 > 0";
            $strCount .= " AND count_1 > 0";
        } else if ($searchData['consignee_type_id'] == 1) {
            $str .= " AND count_2 > 0";
            $strCount .= " AND count_2 > 0";
        }

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        foreach ($res as $r) {
            $r->partner = [];
            $r->myOrganisation = [];
            $r->parent_doc = eCDocuments::where('id', $r->parent_id)
                ->select('id', 'name')
                ->first();
            if ($r->count_1 > 0) {
                $r->myOrganisation = DB::select("SELECT a.full_name as name, a.email, a.state, a.national_id, a.sign_method FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND p.organisation_type = 1 AND a.assign_type = ? and a.status = 1", array($r->id, AssigneeType::SIGN));
            }
            if ($r->count_2 > 0) {
                $r->partner = DB::select("SELECT a.full_name as name, a.email, a.state, a.national_id, a.sign_method FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND p.organisation_type != 1 AND a.assign_type = ? and a.status = 1", array($r->id, AssigneeType::SIGN));
            }
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }
    public function getFatherInfo($id)
    {
        $user = Auth::user();
        $fatherDocument = eCDocuments::where('id', $id)
            ->select('name', 'document_type', 'document_type_id', 'expired_type', 'code', 'doc_expired_date', 'document_sample_id')
            ->first();
        $fatherAssignee = DB::select("SELECT a.full_name as name, a.email, a.state, a.phone, a.noti_type, a.message, a.assign_type, a.national_id, a.sign_method, p.organisation_type, a.company_id FROM ec_document_assignees a JOIN ec_document_partners p ON a.partner_id = p.id WHERE p.document_id = ? AND a.assign_type = ? and a.status = 1", array($id, AssigneeType::SIGN));
        foreach ($fatherAssignee as $f) {
            $f->company_name = '';
            $f->tax_number = '';
            $company = eCCompany::where('id', $f->company_id)
                ->select('name', 'tax_number')
                ->first();
            if ($company) {
                $f->company_name = $company->name;
                $f->tax_number = $company->tax_number;
            }
        }
        return array('fatherDocument' => $fatherDocument, 'fatherAssignee' => $fatherAssignee);
    }

    public function getListCts($user_id)
    {
        $user = Auth::user();
        $url = Common::getConverterServer() . '/api/v1/get-list-credentials';
        $data = array(
            "user_id" => $user_id
        );
        $response = $this->documentHandlingService->sendBackendServer($url, $data);
        //Log::info($response);
        if ($response["status"] != true) {
            $response = $this->documentHandlingService->throwMessage($response["data"]);
            throw new eCBusinessException($response);
        }
        return $response;
    }

    public function registerCTS($request, $document_group)
    {
        $user = Auth::user();
        $ip = $request->ip();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $id = $request->docId;
        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_SIGNING) {
            throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        }
        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)
                ->where('email', $user->email)
                ->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }
        if (!$currentAssignee) {
            throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }

        if ($currentAssignee->national_id != $request->id) {
            throw new eCBusinessException("DOCUMENT.ERR_NOT_MATCH_NATIONAL_ID");
        }

        if ($request->sim < 70) {
            throw new eCBusinessException("DOCUMENT.ERR_NOT_MATCH_FACE");
        }

        DB::beginTransaction();
        try {
            $postData = [
                'national_id' => $request->id,
                'name' => $request->name,
                'birthday' => $request->birthday,
                'sex' => $request->sex,
                'hometown' => $request->hometown,
                'address' => $request->address,
                'issueDate' => $request->issueDate,
                'issueBy' => $request->issueBy,
                'sim' => $request->sim,
            ];
            eCDocumentSignatureKyc::where('assign_id', $currentAssignee->id)->update($postData);

            $url = Common::getConverterServer() . '/api/v1/register-cts';
            $data = array(
                "assigneeId" => $currentAssignee->id
            );
            $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            Log::info($mergeResponse);
            if ($mergeResponse["status"] != true) {
                throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
            }
            DB::commit();

            $data = eCDocumentHsm::where("assignee_id", $currentAssignee->id)->first();
            return array('data' => $data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function signICA($request, $document_group)
    {
        $user = Auth::user();
        $ip = $request->ip();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, $document_group == DocumentType::INTERNAL ? "INTERNAL_SIGN_MANAGE" : "COMMERCE_SIGN_MANAGE", false, false, false, true);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $id = $request->docId;
        $doc = eCDocuments::find($id);
        if (!$doc || $doc->company_id != $user->company_id || $doc->document_state != DocumentState::WAIT_SIGNING) {
            throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        }
        if ($doc->is_order_approval == 0) {
            $currentAssignee = eCDocumentAssignee::where('id', $doc->current_assignee_id)->where('email', $user->email)->first();
        } else {
            $currentAssignee = $this->getCurrentAssigneeConcurrent($id, $doc->document_state, $user);
        }
        if (!$currentAssignee) {
            throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }

        $this->signDocumentHandling($doc, $user, $currentAssignee, 5, $ip);
    }
}
