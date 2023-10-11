<?php


namespace Viettel\Services;

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
use Core\Helpers\ImageHelper;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Customer\Services\Shared\eCDocumentHandlingService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Core\Models\eCDocuments;
use Core\Models\ecDocumentConversations;
use Maatwebsite\Excel\Concerns\ToArray;
use Core\Models\eCUser;
use Core\Services\ActionHistoryService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VTApiService
{
    private $storageHelper;
    private $imageHelper;
    private $documentHandlingService;
    private $permissionService;
    private $actionHistoryService;

    /**
     * eCUtilitiesService constructor.
     * @param StorageHelper $storageHelper
     * @param ImageHelper $imageHelper
     * @param eCDocumentHandlingService $documentHandlingService
     */
    public function __construct(StorageHelper $storageHelper, eCDocumentHandlingService $documentHandlingService, eCPermissionService $permissionService, ActionHistoryService $actionHistoryService, ImageHelper  $imageHelper)
    {
        $this->storageHelper = $storageHelper;
        $this->imageHelper = $imageHelper;
        $this->documentHandlingService = $documentHandlingService;
        $this->permissionService = $permissionService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function getDocumentTemplate() {
        $user = auth('vendor')->user();
        $checkExpired = $this->permissionService->checkExpired($user->company_id);
        if (!$checkExpired) {
            throw new eCBusinessException("SERVER.EXPIRED");
        }
        $lstDocumentSample = DB::table('ec_s_document_samples')
            ->where('company_id', $user->company_id)
            ->where('delete_flag', 0)
            ->select('id', 'name')
            ->get();
        return array("lstTemplate" => $lstDocumentSample, "checkExpired" => $checkExpired);
    }

    public function getDetailTemplate($templateId) {
        $user = auth('vendor')->user();
        $documentSample = DB::table('ec_s_document_samples')
            ->where('id', $templateId)
            ->where('delete_flag', 0)
            ->first();
        if (!$documentSample) {
            throw new eCBusinessException('Mẫu hợp đồng không tồn tại');
        }
        $sampleInfo = DB::select('SELECT * FROM ec_s_document_sample_info WHERE document_sample_id = ? AND (field_code IS NULL OR field_code NOT IN ("its_doc_code", "its_doc_time")) ORDER BY order_assignee asc', array($templateId));
//        $sampleInfo = DB::table('ec_s_document_sample_info')
//            ->where('document_sample_id', $templateId)
//            ->where('field_code', '!=', 'its_doc_code')
////            ->orWhereNull('field_code')
//            ->orderBy('order_assignee', 'asc')
//            ->get();
        $signatures = [];
        $infos = [];
        foreach ($sampleInfo as $info) {
            if ($info->data_type == 1) {
                $text = array(
                    "id" => $info->id,
                    "type" => "text",
                    "field_code" => $info->field_code,
                    "description" => $info->description,
                    "is_required" => $info->is_required == 1,
                    "is_editable" => $info->is_editable == 1,
                );
                array_push($infos, $text);
            } else if ($info->data_type == 2) {
                $signature = array(
                    "id" => $info->id,
                    "description" => $info->description,
                    "full_name" => $info->full_name,
                    "email" => $info->email,
                    "phone" => $info->phone,
                    "national_id" => $info->national_id,
                    "image_signature" => $info->image_signature,
                    "sign_method" => $info->sign_method,
                    "order_assignee" => $info->order_assignee,
                    "is_my_organisation" => $info->is_my_organisation == 1
                );
                array_push($signatures, $signature);
            } else if ($info->data_type == 3) {
                $isExisted = false;
                for ($i = 0; $i < count($infos); $i++) {
                    if ($infos[$i]["type"] == "checkbox" && $infos[$i]["form_name"] == $info->form_name) {
                        array_push($infos[$i]["elements"], array(
                            "id" => $info->id,
                            "field_code" => $info->field_code,
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
                        "field_code" => $info->field_code,
                        "description" => $info->description,
                    ));
                    array_push($infos, $checkbox);
                }
            } else if ($info->data_type == 4) {
                $isExisted = false;
                for ($i = 0; $i < count($infos); $i++) {
                    if ($infos[$i]["type"] == "radio" && $infos[$i]["form_name"] == $info->form_name) {
                        array_push($infos[$i]["elements"], array(
                            "id" => $info->id,
                            "field_code" => $info->field_code,
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
                        "field_code" => $info->field_code,
                        "description" => $info->description,
                    ));
                    array_push($infos, $checkbox);
                }
            }
        }
        return array("infos" => $infos, "signatures" => $signatures);
    }

    public function getDocumentCodeByDocumentTypeId($id, $companyId)
    {
        $documentType = DB::table('s_document_types')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->where('delete_flag', 0)
            ->first();
        if (!$documentType) {
            throw new eCBusinessException('Loại tài liệu không tồn tại');
        }

        $lastDoc = DB::table('ec_documents')
            ->where('document_type_id', $id)
            ->where('parent_id', -1)
            ->orderBy('id', 'desc')
            ->select('code')
            ->first();

        if ($documentType->is_order_auto == 0) {
            return "";
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

    public function createDocumentFromTemplate($request) {
        $user = auth('vendor')->user();
        $ip = $request->ip();

        $document_sample_id = $request->template_id;
        $documentSample = DB::table('ec_s_document_samples')
            ->where('id', $document_sample_id)
            ->where('company_id', $user->company_id)
            ->where('delete_flag', 0)
            ->first();
        if (!$documentSample) {
            throw new eCBusinessException("Mẫu hợp đồng không tồn tại");
        }

//        $code = $request->document_code;
        $name = $request->document_name;
        $customerId = $request->customerId;
        $config = DB::table('ec_s_config_params')->where('company_id', $user->company_id)->first();
        $document_expire_day = !$config ? 60 : $config->document_expire_day;
        $sent_date = date('Y-m-d');
        $expired_date = date('Y-m-d', strtotime($sent_date. ' + ' . $document_expire_day . ' days'));
        $signatures = $request->signatures;
        $infos = $request->infos;

//        if (!$code) {
        $code = $this->getDocumentCodeByDocumentTypeId($documentSample->document_type_id, $user->company_id);
        if ($code == -1) {
            $code = $request->document_code;
        }
//        }
        $postData = [
            'document_sample_id' => $document_sample_id,
            'document_type_id' => $documentSample->document_type_id,
            'code' => $code,
            'name' => $name,
            'sent_date' => $sent_date,
            'expired_date' => $expired_date,
            'is_verify_content' => $documentSample->is_verify_content,
            'expired_type' => $documentSample->expired_type,
            'doc_expired_date' => null,
            'expired_month' => $documentSample->expired_month,
            'source' => 1, //api
            'customer_id' => $customerId,
            'updated_at' => date('Y-m-d H:i:s')
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
        if ($validator->fails()) {
            throw new eCBusinessException("Dữ liệu hợp đồng không hợp lệ");
        }

        $existedCode = DB::table('ec_documents')
            ->where('company_id', $user->company_id)
            ->where('code', $code)
            ->get();
        if (count($existedCode) > 0) {
            throw new eCBusinessException("Mã hợp đồng đã tồn tại");
        }

        $sampleInfo = DB::table('ec_s_document_sample_info')
            ->where('document_sample_id', $document_sample_id)
            ->where('data_type', '!=', 2)
            ->get();

        foreach ($sampleInfo as $info) {
            if ($info->is_required == 1) {
                $isExisted = false;
                for ($i = 0; $i < count($infos); $i++) {
                    if ($info->id == $infos[$i]["id"]) {
                        if (isset($infos[$i]["content"]) && $infos[$i]["content"] != "") {
                            $isExisted = true;
                            break;
                        }
                    }
                }
                if (!$isExisted) {
                    throw new eCBusinessException($info->description . " bắt buộc nhập");
                }
            }
        }

        $sampleSignatures = DB::table('ec_s_document_sample_info')
            ->where('document_sample_id', $document_sample_id)
            ->where('data_type', 2)
            ->orderBy('order_assignee', 'asc')
            ->get();
        foreach ($sampleSignatures as $signature) {
            if ($signature->is_my_organisation == 0) {
                $isExisted = false;
                for ($i = 0; $i < count($signatures); $i++) {
                    $sign = $signatures[$i];
                    if ($sign["id"] == $signature->id) {
                        $signData = [
                            'full_name' => $sign["full_name"],
                            'email' => $sign["email"],
                            'phone' => $sign["phone"],
                            'national_id' => $sign["national_id"]
                        ];
                        $signatureRules = [
                            'full_name' => 'required|max:255',
                            'phone' => 'nullable|digits_between:8,15',
                            'national_id' => 'required|max:50',
                            'email' => 'required|email|max:255'
                        ];
                        $validatorSign = Validator::make($signData, $signatureRules);
                        if ($validatorSign->fails()) {
                            throw new eCBusinessException("Dữ liệu người giao kết không hợp lệ");
                        }
                        $isExisted = true;
                        break;
                    }
                }
                if (!$isExisted) {
                    throw new eCBusinessException($signature->description . " bắt buộc nhập");
                }
            }
        }
        // $code = "132/11/22-KKAA";

        DB::beginTransaction();
        try {
            $doc = new eCDocuments();
            $doc->document_sample_id = $document_sample_id;
            $doc->document_type_id = $documentSample->document_type_id;
            $doc->document_type = $documentSample->document_type;
            $doc->code = $code;
            $doc->name = $name;
            $doc->sent_date = $sent_date;
            $doc->expired_date = $expired_date;
            $doc->is_verify_content = $documentSample->is_verify_content;
            $doc->source = 1;
            $doc->customer_id = $customerId;
            $doc->updated_by = -1;
            $doc->company_id = $user->company_id;
            $doc->created_by = -1;
            $doc->status = 1;
            $doc->document_state = DocumentState::DRAFT;
            $doc->save();

            $id = $doc->id;

            DB::table("ec_document_assignees")->insert([
                "company_id" => $user->company_id,
                "full_name" => $user->username . '-api',
                "email" => "-1",
                "document_id" => $id,
                "partner_id" => "-1",
                "message" => "",
                "noti_type" => 0,
                "assign_type" => AssigneeType::CREATOR,
                'state' => 2, //da giao ket vi tao tai lieu thi da lien quan den tai lieu
                "created_by" => $customerId
            ]);

            $destinationPath = '/internal/' . $id . '/';
            $fileName = time(). '.pdf';

            $file = $this->storageHelper->copyFile($documentSample->document_path_original, $destinationPath, $fileName);

            DB::table('ec_document_resources_ex')->insert([
                "company_id" => $user->company_id,
                "document_id" => $id,
                "parent_id" => -1,
                "document_path_original" => $destinationPath . $fileName,
                "document_path_sign" => $destinationPath . $fileName,
                "created_by" => -1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $signatureDatas = array();
            $signatureImages = array();

            $company = DB::table("ec_companies")
                ->where('id', $user->company_id)
                ->select('name', 'tax_number')
                ->first();
            foreach ($sampleSignatures as $signature) {

                if ($signature->is_my_organisation == 1) {
                    $partnerId = DB::table('ec_document_partners')->insertGetId([
                        "document_id" => $id,
                        "order_assignee" => $signature->order_assignee,
                        "organisation_type" => 1,
                        "company_name" => $signature->is_my_organisation == 1 ? $company->name : "",
                        "tax" => $signature->is_my_organisation == 1 ? $company->tax_number : "",
                        "created_by" => -1
                    ]);
                    $assignee_id = DB::table("ec_document_assignees")->insertGetId([
                        "company_id" => $user->company_id,
                        "full_name" => $signature->full_name,
                        "email" => $signature->email,
                        "phone" => $signature->phone,
                        "document_id" => $id,
                        "partner_id" => $partnerId,
                        "message" => "",
                        "noti_type" => 1,
                        "assign_type" => 2,
                        "national_id" => $signature->national_id,
                        "sign_method" => $signature->sign_method,
                        "created_by" => $customerId,
                        'is_auto_sign' => $signature->is_auto_sign
                    ]);
                    array_push($signatureDatas, [
                        "document_id" => $id,
                        "assign_id" => $assignee_id,
                        "page_sign" => $signature->page_sign,
                        "width_size" => $signature->width_size,
                        "height_size" => $signature->height_size,
                        "x" => $signature->x,
                        "y" => $signature->y,
                        "page_width" => $signature->page_width,
                        "page_height" => $signature->page_height,
                        "created_by" => -1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_by' => -1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    array_push($signatureImages, [
                        "assign_id" => $assignee_id,
                        "image_signature" => $signature->image_signature
                    ]);
                } else {
                    for ($i = 0 ; $i < count($signatures); $i++) {
                        $sign = $signatures[$i];
                        if ($sign["id"] == $signature->id) {
                            $organizationType = 3;
                            if (isset($sign["organization_type"])) {
                                $organizationType = $sign["organization_type"] == 1 ? 2 : 3;
                            }
                            $partnerId = DB::table('ec_document_partners')->insertGetId([
                                "document_id" => $id,
                                "order_assignee" => $signature->order_assignee,
                                "organisation_type" => $organizationType,
                                "company_name" => $organizationType == 2 ? $sign["company_name"] : "",
                                "tax" => $organizationType == 2 ? $sign["company_tax"] : "",
                                "created_by" => -1
                            ]);
                            $assignee_id = DB::table("ec_document_assignees")->insertGetId([
                                "company_id" => $user->company_id,
                                "full_name" => $sign["full_name"],
                                "email" => $sign["email"],
                                "phone" => $sign["phone"],
                                "document_id" => $id,
                                "partner_id" => $partnerId,
                                "message" => "",
                                "noti_type" => $signature->noti_type,
                                "assign_type" => 2,
                                "national_id" => $sign["national_id"],
                                "sign_method" => $signature->sign_method,
                                "created_by" => $customerId,
                                'is_auto_sign' => 0
                            ]);
                            array_push($signatureDatas, [
                                "document_id" => $id,
                                "assign_id" => $assignee_id,
                                "page_sign" => $signature->page_sign,
                                "width_size" => $signature->width_size,
                                "height_size" => $signature->height_size,
                                "x" => $signature->x,
                                "y" => $signature->y,
                                "page_width" => $signature->page_width,
                                "page_height" => $signature->page_height,
                                "created_by" => -1,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_by' => -1,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

//                            array_push($signatureImages, [
//                                "assign_id" => $assignee_id,
//                                "image_signature" => $sign["image_signature"]
//                            ]);
                            break;
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
                        if($info->description){
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
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_by' => -1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    for ($i = 0 ; $i < count($infos); $i++) {
                        $userInfo = $infos[$i];
                        if ($info->id == $userInfo["id"]) {
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
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_by' => -1,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }

            DB::table("ec_document_signature")->insert($signatureDatas);
            DB::table("ec_document_text_info")->insert($textDatas);
            DB::table('ec_document_signature_kyc')->insert($signatureImages);

            $this->actionHistoryService->SetActivity(HistoryActionType::API_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'INSERT_DOCUMENT', 'Tạo mới tài liệu mã ' . $doc->code , json_encode($postData));
            $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::FINISH_DRAFTING, "Tạo tài liệu thành công", "", "");

            DB::commit();

            if (count($textDatas) > 0) {
                $url = Common::getConverterServer() . '/api/v1/document/sign-text';
                $data = array(
                    "assign_id" => -1,
                    "document_id" => $id
                );
                $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
                Log::info($mergeResponse);
                if ($mergeResponse["status"] != true) {
                    throw new eCBusinessException("Có lỗi xảy ra khi tạo hợp đồng");
                }
            }

            $doc = eCDocuments::find($id);

            $currentAssignee = $this->documentHandlingService->getNextAssignee($id, $user->company_id);
            if ($currentAssignee->assign_type == AssigneeType::SIGN && $currentAssignee->is_auto_sign == 1) {
                $doc->updated_by = $user->id;
                $doc->current_assignee_id = $currentAssignee->id;
                $doc->save();
                DB::commit();
                $this->handleSignDocument($doc, $user, $ip, 0);
                return array("document_id" => $id);
            } else {
                $password = Common::randomString(6);
                $urlCode = Common::randomString(10) . "-" . time();
                if ($currentAssignee->assign_type == AssigneeType::APPROVAL || $currentAssignee->assign_type == AssigneeType::SIGN) {
                    DB::table('ec_document_assignees')
                        ->where('id', $currentAssignee->id)
                        ->update([
                            "password" => Hash::make($password),
                            "url_code" => $urlCode
                        ]);
                }
                $sign_remote = [
                    "name" => $currentAssignee->full_name,
                    "email" => $currentAssignee->email,
                    "password" => $password,
                    "url_code" => $urlCode
                ];
                $sendParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $currentAssignee->message);
                $this->actionHistoryService->SetActivity(HistoryActionType::API_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'CREATE_SIGN_REMOTE', 'Tạo đường dẫn giao kết ngoài cho tài liệu mã ' . $doc->code, json_encode($sign_remote));
                if ($currentAssignee->assign_type == AssigneeType::APPROVAL) {
                    if($doc->parent_id != -1){
                        $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $doc->id, NotificationType::APPROVAL_REQUEST_ADDENDUM, $sendParams);
                    }else{
                        $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $doc->id, NotificationType::APPROVAL_REQUEST_DOCUMENT, $sendParams);
                    }
                    $doc->document_state = DocumentState::WAIT_APPROVAL;
                } else if ($currentAssignee->assign_type == AssigneeType::SIGN) {
                    if($doc->parent_id != -1){
                        $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $doc->id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendParams);
                    }else{
                        $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $doc->id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendParams);
                    }
                    $doc->document_state = DocumentState::WAIT_SIGNING;
                }
                $doc->updated_by = $user->id;
                $doc->current_assignee_id = $currentAssignee->id;
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "Gửi email cho người tham gia " . $currentAssignee->full_name . " thành công", "", "");

                $doc->save();
                DB::commit();
                return array("document_id" => $id);
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            if(str_contains($e, 'exists_code_unique')){
                $this->createDocumentFromTemplate($request);
            }
            throw new eCBusinessException("Có lỗi xảy ra khi tạo hợp đồng");
        }
    }

    public function searchDocumentList($searchData, $start, $limit, $customerId){
        $user = auth('vendor')->user();
        $arr = array();
        $str = 'SELECT distinct(d.id), d.document_state, d.expired_date, d.finished_date, d.name, d.code, p.organisation_type FROM ec_documents d LEFT JOIN ec_document_partners p ON d.id = p.document_id AND p.organisation_type != 1 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_documents d';

        $str .= " WHERE d.company_id = ? AND d.delete_flag = 0 and d.customer_id = ? ";
        $strCount .= " WHERE d.company_id = ? AND d.delete_flag = 0 and d.customer_id = ? ";
        array_push($arr, $user->company_id);
        array_push($arr, $customerId);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            $strCount .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
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

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("recordsTotal" => $resCount[0]->cnt, "lstDocuments" => $res);
    }

    public function getViewDocument($id, $customerId) {
        $user = auth('vendor')->user();
        $document = DB::table('ec_documents as d')
            ->join('s_document_types as dt', 'd.document_type_id', 'dt.id')
            ->where('d.id', $id)
            ->where('d.company_id', $user->company_id)
            ->where('document_state', '!=', DocumentState::DRAFT)
            ->where('d.delete_flag', 0)
            ->where('customer_id', $customerId)
            ->select('d.id', 'dt.dc_type_name as type_name', 'd.name', 'd.code', 'd.document_state', 'd.current_assignee_id', 'd.sent_date', 'd.expired_date', 'd.finished_date', 'd.created_at', 'd.is_verify_content')
            ->first();
        if (!$document) {
            throw new eCBusinessException("Hợp đồng không tồn tại");
        }

        $document->partners = DB::table("ec_document_partners")
            ->where('document_id', $id)
            ->where('status', 1)
            ->orderBy("order_assignee", "asc")
            ->select('id', 'company_name', 'tax', 'order_assignee', 'organisation_type')
            ->get();
        foreach ($document->partners as $partner) {
            $partner->assignees = DB::table("ec_document_assignees")
                ->where('partner_id', $partner->id)
                ->whereIn('assign_type', [AssigneeType::APPROVAL, AssigneeType::SIGN, AssigneeType::STORAGE])
                ->where('status', 1)
                ->orderBy('assign_type', 'asc')
                ->select('id', 'full_name', 'email', 'phone', 'national_id', 'assign_type', 'state')
                ->get();
        }

        if ($document->current_assignee_id) {
            $document->currentAssignee = DB::table('ec_document_assignees')
                ->where('id', $document->current_assignee_id)
                ->select('id', 'full_name', 'email', 'phone', 'national_id', 'assign_type', 'state')
                ->first();
        }

        return array('document' => $document);
    }

    public function downloadDocument($id, $customerId) {
        $user = auth('vendor')->user();
        $doc = DB::table('ec_documents')
            ->where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('customer_id', $customerId)
            ->first();
        if (!$doc) {
            throw new eCBusinessException("Document is not existed");
        }

        $file = DB::table("ec_document_resources_ex")->where('document_id', $id)->where('status', 1)->first();
        return $this->storageHelper->downloadFile($file->document_path_sign);
    }

    public function checkIdCard($customerId, $image) {
        $user = auth('vendor')->user();
        $url = Common::getConverterServer() . '/api/v1/orc/check';
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace('data:image/jpg;base64,', '', $image);
        $data = array(
            "image_card" => $image,
            'customer_id' => $customerId,
            'company_id' => $user->company_id
        );
        $checkResponse = $this->documentHandlingService->sendBackendServer($url, $data);
        Log::info($checkResponse);
        if ($checkResponse["status"] != true) {
            throw new eCBusinessException("Kiểm tra chất lượng ảnh GTTT không thành công");
        }
        $res = json_decode($checkResponse["data"]);
        $resData = array(
            "code" => $res->data->code,
            "message" => $res->data->message
        );
        return $resData;
    }

    public function ocrIdCard($customerId, $image_front, $image_back) {
        $user = auth('vendor')->user();
        $customerInfo = DB::table('vtp_customer_info')->where('customer_id', $customerId)->first();
        $pathFront = $this->storageHelper->uploadBase64File($image_front, '/customer/' . $customerId . '/');
        $pathFront = $this->imageHelper->resizeThumb($pathFront);
        $pathBack = $this->storageHelper->uploadBase64File($image_back, '/customer/' . $customerId . '/');
        $pathBack = $this->imageHelper->resizeThumb($pathBack);
        if ($customerInfo) {
            DB::table('vtp_customer_info')
                ->where('customer_id', $customerId)
                ->update([
                    'front_image_url' => $pathFront,
                    'back_image_url' => $pathBack
                ]);
        } else {
            DB::table('vtp_customer_info')->insert([
                'customer_id' => $customerId,
                'front_image_url' => $pathFront,
                'back_image_url' => $pathBack
            ]);
        }
        $url = Common::getConverterServer() . '/api/v1/orc/ocr';
        $data = array(
            "customer_id" => $customerId,
            'company_id' => $user->company_id
        );
        $checkResponse = $this->documentHandlingService->sendBackendServer($url, $data);
        Log::info($checkResponse);
        if ($checkResponse["status"] != true) {
            throw new eCBusinessException("Bóc tách thông tin GTTT không thành công");
        }
        $res = json_decode($checkResponse["data"]);
        $resData = array(
            "code" => $res->data->code,
            "message" => $res->data->message,
            "information" => $res->data->information,
        );
        return $resData;
    }

    public function signEkyc($docId, $customerId, $face_image, $ip, $imageSignature) {
        $user = auth('vendor')->user();
        $doc = eCDocuments::find($docId);
        if (!$doc || $doc->company_id != $user->company_id) {
            throw new eCBusinessException("Hợp đồng không tồn tại");
        } else {
            if ($doc->document_state == DocumentState::COMPLETE || $doc->document_state == DocumentState::NOT_AUTHORIZE || $doc->document_state == DocumentState::COMPLETING || $doc->document_state == DocumentState::VERIFY_FAIL) {
                throw new eCBusinessException("Hợp đồng đã được ký");
            } else if ($doc->document_state == DocumentState::DENY) {
                throw new eCBusinessException("Hợp đồng đã bị từ chối");
            } else if ($doc->document_state == DocumentState::OVERDUE || $doc->document_state == DocumentState::DROP) {
                throw new eCBusinessException("Hợp đồng đã quá hạn hoặc bị hủy bỏ");
            } else if ($doc->document_state != DocumentState::WAIT_SIGNING) {
                throw new eCBusinessException("Hợp đồng không tồn tại");
            }
        }
        $currentAssignee = DB::table('ec_document_assignees as a')
            ->join('ec_document_partners as p', 'a.partner_id', 'p.id')
            ->where('a.id', $doc->current_assignee_id)
            ->where('p.organisation_type', '!=', 1)
            ->where('a.created_by', $customerId)
            ->select('a.*')
            ->first();
        if (!$currentAssignee) {
            throw new eCBusinessException("Không có quyền ký tài liệu");
        }

        $customerInfo = DB::table('vtp_customer_info')
            ->where('customer_id', $customerId)
            ->first();
        if (!$customerInfo) {
            throw new eCBusinessException("Thông tin khách hàng không tồn tại");
        }

        $assigneeSignature = DB::table('ec_document_signature_kyc')
            ->where('assign_id', $currentAssignee->id)
            ->first();
        if (!$assigneeSignature && !$imageSignature) {
            throw new eCBusinessException("Hình ảnh chữ ký chưa được thiết lập");
        }
        $img = "";
        if ($imageSignature) {
            $img = $imageSignature;
        } else {
            $img = $assigneeSignature->image_signature;
        }
        $facePath = $this->storageHelper->uploadBase64File($face_image, '/customer/' . $customerId . '/');
        if ($assigneeSignature) {
            DB::table('ec_document_signature_kyc')
                ->where('assign_id', $currentAssignee->id)
                ->update([
                    'image_signature' => $img,
                    'front_image_url' => $customerInfo->front_image_url,
                    'back_image_url' => $customerInfo->back_image_url,
                    'face_image_url' => $facePath
                ]);
        } else {
            DB::table('ec_document_signature_kyc')
                ->insert([
                    'assign_id' => $currentAssignee->id,
                    'image_signature' => $img,
                    'front_image_url' => $customerInfo->front_image_url,
                    'back_image_url' => $customerInfo->back_image_url,
                    'face_image_url' => $facePath
                ]);
        }

        $url = Common::getConverterServer() . '/api/v1/orc/verify/vtp';
        $data = array(
            "assignee_id" => $currentAssignee->id
        );
        $checkResponse = $this->documentHandlingService->sendBackendServer($url, $data);
        if ($checkResponse["status"] != true) {
            throw new eCBusinessException("Nhận dạng người ký không thành công");
        }
        $res = json_decode($checkResponse["data"]);
        if ($res->data->code != 1) {
            throw new eCBusinessException($res->data->message);

        } else {
            if ($res->data->score < 0.7) {
                throw new eCBusinessException("Nhận dạng ảnh khuôn mặt không khớp với GTTT");
            }
            $resData = array(
                "code" => $res->data->code,
                "message" => $res->data->message,
                "verify_result" => $res->data->verify_result,
                "score" => $res->data->score,

            );
            return $resData;
        }

        $this->handleSignDocument($doc, $user, $ip, 2);
    }

    public function handleSignDocument($doc, $user, $ip, $signType) {
        DB::beginTransaction();
        try {

            $submitTime = date('Y-m-d H:i:s');
            DB::table('ec_document_assignees')
                ->where('id', $doc->current_assignee_id)
                ->update([
                    'state' => 2, //da giao ket
                    'submit_time' => $submitTime
                ]);
            $nextAssignee = $this->documentHandlingService->getNextAssignee($doc->id, $user->company_id);

            $url = Common::getConverterServer() . '/api/v1/document/sign';
            $data = array(
                "assign_id" => $doc->current_assignee_id,
                "document_id" => $doc->id,
                "sign_type" => $signType,
                "sign_action" => $nextAssignee ? 0 : 1,
                "ca" => ""

            );
            $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            Log::info($mergeResponse);
            if ($mergeResponse["status"] != true) {
                throw new eCBusinessException("Ký hợp đồng không thành công");
            }
            $raw_log = [
                "assignee" => $nextAssignee->full_name,
                "assignee_email" => $nextAssignee->email,
                "message" => $nextAssignee->message,
                "doc" => $doc,
            ];
            $this->actionHistoryService->SetActivity(HistoryActionType::API_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'SIGN_DOCUMENT', 'Ký tài liệu mã ' . $doc->code, json_encode($raw_log));

            //TODO: gui email thong bao da phe duyet

            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($doc->id, $user->full_name, $submitTime, "", "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($doc->id);
            if($doc->parent_id != -1){
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $doc->id, NotificationType::AGREE_SIGN_ADDENDUM, $sendBeforeAssigneeParams);
            }else{
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $doc->id, NotificationType::AGREE_SIGN_DOCUMENT, $sendBeforeAssigneeParams);
            }
            $nextAssignee = $this->documentHandlingService->getNextAssignee($doc->id, $user->company_id);
            if (!$nextAssignee) {
                $doc->current_assignee_id = NULL;
                if ($doc->is_verify_content) {
                } else {
                    $doc->document_state = DocumentState::COMPLETE;
                    $doc->finished_date = date('Y-m-d H:i:s');
                    if($doc->expired_type == 2){
                        $doc->doc_expired_date = date('Y-m-d H:i:s',strtotime('+'.$doc->expired_month.' month',strtotime($doc->finished_date)));
                    }
                    if($doc->parent_id != -1){
                        if($doc->addendum_type == 1){
                            $parent_doc = DB::table('ec_documents')->where('id',$doc->parent_id)->first();
                                if($doc->expired_type == 0){
                                    DB::table('ec_documents')->where('id',$doc->parent_id)
                                    ->update([
                                        'expired_type' => 0,
                                        'doc_expired_date'=> null
                                    ]);
                                } else if ($doc->expired_type == 1) {
                                    DB::table('ec_documents')->where('id',$doc->parent_id)
                                    ->update([
                                        'expired_type' => 1,
                                        'doc_expired_date' => $doc->doc_expired_date
                                    ]);
                                } else if ($doc->expired_type == 2) {
                                    DB::table('ec_documents')->where('id',$doc->parent_id)
                                    ->update([
                                        'expired_type' => 1,
                                        'doc_expired_date' => date('Y-m-d H:i:s',strtotime('+'.$doc->expired_month.' month',strtotime($parent_doc->doc_expired_date)))
                                    ]);
                                }
                        }
                        if($doc->addendum_type == 2){
                            DB::table('ec_documents')->where('id',$doc->parent_id)
                                    ->update([
                                        'document_state' => DocumentState::DROP,
                                    ]);
                        }
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'COMPLETE_DOCUMENT', 'Hoàn thành phụ lục mã ' . $doc->code, json_encode($doc));
                    } else {
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'COMPLETE_DOCUMENT', 'Hoàn thành tài liệu mã ' . $doc->code, json_encode($doc));
                    }
                    DB::commit();
                }
            } else {
                if ($nextAssignee->assign_type == AssigneeType::SIGN && $nextAssignee->is_auto_sign == 1) {
                    $doc->current_assignee_id = $nextAssignee->id;
                    $doc->save();
                    DB::commit();
                    $this->handleSignDocument($doc, $user, $ip, 2);
                } else {
                    $password = Common::randomString(6);
                    $urlCode = Common::randomString(10) . "-" . time();
                    if ($nextAssignee->assign_type == 1 || $nextAssignee->assign_type == 2) {
                        DB::table('ec_document_assignees')
                            ->where('id', $nextAssignee->id)
                            ->update([
                                "password" => Hash::make($password),
                                "url_code" => $urlCode
                            ]);
                    }
                    $sign_remote = [
                        "name" => $nextAssignee->full_name,
                        "email" => $nextAssignee->email,
                        "password" => $password,
                        "url_code" => $urlCode
                    ];
                    $this->actionHistoryService->SetActivity(HistoryActionType::API_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'CREATE_SIGN_REMOTE', 'Tạo đường dẫn giao kết ngoài cho tài liệu mã ' . $doc->code, json_encode($sign_remote));
                    $sendNextAssigneeParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
                    if($doc->parent_id != -1){
                        $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $doc->id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendNextAssigneeParams);
                    }else{
                        $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $doc->id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendNextAssigneeParams);
                    }
                    $doc->current_assignee_id = $nextAssignee->id;
                    $this->documentHandlingService->insertDocumentLog($doc->id, DocumentLogStatus::SEND_EMAIL, "Gửi email cho người tham gia " . $nextAssignee->full_name . " thành công", "", "");
                }
            }

            $doc->save();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw new eCBusinessException($e->getMessage());
        }
    }
}
