<?php


namespace Customer\Services;


use Core\Helpers\AssigneeType;
use Core\Helpers\Common;
use Core\Helpers\DocumentLogStatus;
use Core\Helpers\DocumentState;
use Core\Helpers\DocumentType;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Helpers\NotificationType;
use Core\Helpers\StorageHelper;
use Core\Helpers\ImageHelper;
use Core\Models\eCDocumentAssignee;
use Core\Models\eCDocumentHsm;
use Core\Models\eCDocumentPartners;
use Core\Models\eCDocumentResourcesEx;
use Core\Models\eCDocuments;
use Core\Models\eCDocumentSignature;
use Core\Models\eCDocumentSignatureKyc;
use Core\Models\eCDocumentTutorial;
use Core\Models\eCDocumentTutorialResources;
use Core\Models\eCGuideVideo;
use Core\Models\eCUser;
use Core\Services\eContractBaseService;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Queues\PTApiLoginEvent;
use Customer\Services\Shared\eCDocumentHandlingService;
use Customer\Services\Shared\eCPermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Core\Services\ActionHistoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class eCDocumentAssigneeService extends eContractBaseService
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
        parent::__construct(eCDocumentAssigneeService::class);
        $this->permissionService = $permissionService;
        $this->storageHelper = $storageHelper;
        $this->documentHandlingService = $documentHandlingService;
        $this->actionHistoryService = $actionHistoryService;
        $this->imageHelper = $imageHelper;
    }

    public function updateAccessToken($user)
    {
        return [
            'account' => $user->id,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'sex' => $user->sex,
            'status' => $user->status,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'token_type' => 'bearer',
            'token' => $user->token,
            'expires_in' => auth('assign')->factory()->getTTL() * 60
        ];
    }

    public function initViewDocument() {
        $user = auth('assign')->user();
        $document = DB::table('ec_documents as d')
            ->join('s_document_types as dt', 'd.document_type_id', 'dt.id')
            ->join('ec_document_assignees as a', 'd.id', 'a.document_id')
            ->where('a.assign_type', AssigneeType::CREATOR)
            ->where('d.id', $user->document_id)
            ->where('document_state', '!=', DocumentState::DRAFT)
            ->where('d.delete_flag', 0)
            ->select('d.id', 'dt.dc_type_name as type_name', 'd.name', 'd.code', 'd.document_state', 'd.is_order_approval', 'd.current_assignee_id', 'd.sent_date', 'd.expired_date', 'd.finished_date', 'd.created_at', 'a.full_name as creator_name', 'a.email as creator_email')
            ->first();
        if (!$document) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        $document->partners = eCDocumentPartners::where('document_id', $user->document_id)
            ->where('status', 1)
            ->orderBy("order_assignee", "asc")->get();
        foreach ($document->partners as $partner) {
            $partner->assignees = eCDocumentAssignee::where('partner_id', $partner->id)
                ->whereIn('assign_type', [AssigneeType::APPROVAL, AssigneeType::SIGN, AssigneeType::STORAGE])
                ->where('status', 1)
                ->get();
        }

        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id) {
                $document->cur = eCDocumentAssignee::with('partner')
                    ->where('id', $document->current_assignee_id)
                    ->first();
            }
            if ($document->document_state == DocumentState::WAIT_SIGNING) {
                if ($document->cur && $document->cur->email == $user->email) {
                    $document->cur->signature = eCDocumentSignatureKyc::where('assign_id', $document->current_assignee_id)->first();
                    $document->signature_location = eCDocumentSignature::where('assign_id', $document->current_assignee_id)->get();
                }
            }
        } else {
            if ($user->state == 0 || $user->state == 1) {
                if (($user->assign_type == AssigneeType::APPROVAL && $document->document_state == DocumentState::WAIT_APPROVAL) ||
                    ($user->assign_type == AssigneeType::SIGN && $document->document_state == DocumentState::WAIT_SIGNING)) {
                    $document->cur = eCDocumentAssignee::with('partner')
                        ->where('id', $user->id)
                        ->first();
                    if ($document->document_state == DocumentState::WAIT_SIGNING) {
                        if ($document->cur) {
                            $document->cur->signature = eCDocumentSignatureKyc::where('assign_id', $user->id)->first();
                            $document->signature_location = eCDocumentSignature::where('assign_id', $user->id)->get();
                        }
                    }
                }
            }
        }
        return array('document' => $document, 'user'=>$user);
    }

    public function getSignDocument($id)
    {
        $user = auth('assign')->user();
        $file = eCDocumentResourcesEx::where('document_id', $id)->where('status', 1)->first();
        return $this->storageHelper->downloadFile($file->document_path_sign);
    }

    public function updateSignature($image_signature, $docId)
    {
        $user = auth('assign')->user();

        $document = eCDocuments::find($user->document_id);
        if (!$document) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id != $user->id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        } else {
            if (!($user->assign_type == AssigneeType::SIGN && $document->document_state == DocumentState::WAIT_SIGNING)) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }

        DB::beginTransaction();
        try {
            eCDocumentSignatureKyc::where('assign_id', $user->id)->delete();
            //TODO: luu anh chu ky tu plugin
            $postData=[
                "assign_id" => $user->id,
                "image_signature" => $image_signature
            ];
            eCDocumentSignatureKyc::create($postData);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function saveSignatureLocation($lstLocation) {
        $user = auth('assign')->user();
        $document = eCDocuments::find($user->document_id);
        if (!$document) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id != $user->id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        } else {
            if (!($user->assign_type == AssigneeType::SIGN && $document->document_state == DocumentState::WAIT_SIGNING)) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }
        try {
            eCDocumentSignature::where('assign_id', $user->id)->delete();
            $signatureDatas = array();
            foreach ($lstLocation as $signature) {
                array_push($signatureDatas, [
                    "document_id" => $document->id,
                    "assign_id" => $user->id,
                    "page_sign" => $signature["Page"],
                    "width_size" => $signature["Width"],
                    "height_size" => $signature["Height"],
                    "x" => $signature["XAxis"],
                    "y" => $signature["YAxis"],
                    "page_width" => $signature["pageWidth"],
                    "page_height" => $signature["pageHeight"],
                    "created_by" => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
            eCDocumentSignature::create($signatureDatas);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function approveDocument($id, $reason = "") {
        $user = auth('assign')->user();
        if ($id != $user->document_id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        $doc = eCDocuments::find($id);
        if (!$doc || $doc->document_state != DocumentState::WAIT_APPROVAL) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            if ($doc->current_assignee_id != $user->id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        } else {
            if (!($user->assign_type == AssigneeType::APPROVAL && $doc->document_state == DocumentState::WAIT_APPROVAL) && !($user->assign_type == AssigneeType::SIGN && $doc->document_state == DocumentState::WAIT_SIGNING)) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }
        $creator = eCUser::find($doc->created_by);
        DB::beginTransaction();
        try {
            $submitTime = date('Y-m-d H:i:s');
            $postData =[
                'id' => $user->id,
                'state' => 2, //da giao ket
                'reason' => $reason,
                'submit_time' => $submitTime
            ];
            $raw_log = [
                'assign' => $user->full_name,
                'assign_email' => $user->email,
                'reason' => $reason,
                'submit_time' => $submitTime
            ];
            eCDocumentAssignee::find($user->id)->update($postData);
            //TODO: gui email thong bao da phe duyet

            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($id, $user->full_name, $submitTime, $reason, "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($id);
            if($doc->parent_id != -1){
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::AGREE_APPROVAL_ADDENDUM, $sendBeforeAssigneeParams);
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'APPROVE_ADDENDUM', $doc->code, json_encode($raw_log));
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::AGREE_APPROVAL, "AGREE_APPROVAL_ADDENDUM", $user->full_name, $user->email);
                }else{
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::AGREE_APPROVAL_DOCUMENT, $sendBeforeAssigneeParams);
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'APPROVED_DOCUMENT', $doc->code, json_encode($raw_log));
                $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::AGREE_APPROVAL, "AGREE_APPROVAL_DOCUMENT", $user->full_name, $user->email);
            }


            if ($doc->is_order_approval == 0) {
                $nextAssignee = $this->documentHandlingService->getNextAssignee($id, $doc->company_id);
                if (!$nextAssignee) {
                    throw new eCBusinessException("SERVER.NOT_SIGNATURE_PARTICIPANT");
                } else {
                    $password = Common::randomString(6);
                    $urlCode = Common::randomString(10) . "-" . time();
                    $pushData = [
                        'id' => $nextAssignee->id,
                        "password" => Hash::make($password),
                        "url_code" => $urlCode
                    ];
                    if ($nextAssignee->assign_type == AssigneeType::APPROVAL || $nextAssignee->assign_type == AssigneeType::SIGN) {
                        DB::table('ec_document_assignees')
                            ->where('id', $nextAssignee->id)
                            ->update($pushData);
                    }
                    $sendNextAssigneeParams = $this->documentHandlingService->getExts($id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
                    if ($nextAssignee->assign_type == AssigneeType::SIGN) {
                        if($doc->parent_id != -1){
                            $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendNextAssigneeParams);
                        }else{
                            $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendNextAssigneeParams);
                        }
                        $doc->document_state = DocumentState::WAIT_SIGNING;
                    } else {
                        if($doc->parent_id != -1){
                            $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::APPROVAL_REQUEST_ADDENDUM, $sendNextAssigneeParams);
                        }else{
                            $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::APPROVAL_REQUEST_DOCUMENT, $sendNextAssigneeParams);
                        }
                    }
                    $doc->current_assignee_id = $nextAssignee->id;
                    $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $nextAssignee->full_name, $doc->created_by == -1 ? "" : $creator->name, $doc->created_by == -1 ? "" : $creator->email);
                }
            } else {
                $assigneeElse = $this->documentHandlingService->getAllAssigneeByType($id, AssigneeType::APPROVAL);
                if (count($assigneeElse) == 0) {
                    $nextAssignees = $this->documentHandlingService->getAllAssigneeByType($id, AssigneeType::SIGN);
                    foreach ($nextAssignees as $assign) {
                        $password = Common::randomString(6);
                        $urlCode = Common::randomString(10) . "-" . time();
                        eCDocumentAssignee::find($assign->id)
                            ->update([
                                "password" => Hash::make($password),
                                "url_code" => $urlCode
                            ]);
                        $sendParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $assign->message);
                        if($doc->parent_id != -1){
                            $this->documentHandlingService->sendNotificationApi([$assign->id], $doc->id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendParams);
                        }else{
                            $this->documentHandlingService->sendNotificationApi([$assign->id], $doc->id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendParams);
                        }
                        $doc->document_state = DocumentState::WAIT_SIGNING;
                        $doc->updated_by = $user->id;
                        $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $assign->full_name, $user->name, $user->email);
                    }
                }
            }

            $doc->save();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }


    public function transferDocument($request) {
        $user = auth('assign')->user();
        $id = $request->docId;
        if ($id != $user->document_id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        $doc = eCDocuments::find($id);
        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            if ($doc->current_assignee_id != $user->id)  throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        } else {
            if (!($user->assign_type == AssigneeType::APPROVAL && $doc->document_state == DocumentState::WAIT_APPROVAL) &&
                !($user->assign_type == AssigneeType::SIGN && $doc->document_state == DocumentState::WAIT_SIGNING)) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        }
        DB::beginTransaction();
        try {
            $password = Common::randomString(6);
            $urlCode = Common::randomString(10) . "-" . time();

            $nextAssignee = new eCDocumentAssignee();
            $nextAssignee->company_id = $user->company_id;
            $nextAssignee->full_name = $request->name;
            $nextAssignee->email = Str::lower($request->email);
            $nextAssignee->phone = $request->phone;
            $nextAssignee->national_id = $request->national_id;
            $nextAssignee->message = $request->message;
            $nextAssignee->noti_type = 1;
            $nextAssignee->document_id = $user->document_id;
            $nextAssignee->partner_id = $user->partner_id;
            $nextAssignee->assign_type = $user->assign_type;
            $nextAssignee->sign_method = $user->sign_method;
            $nextAssignee->password = Hash::make($password);
            $nextAssignee->url_code = $urlCode;
            $nextAssignee->save();
            if($doc->parent_id != -1){
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'TRANSFER_ASSIGNEE_ADDENDUM', $doc->code, json_encode($nextAssignee));
            }else{
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'TRANSFER_ASSIGNEE_DOCUMENT', $doc->code, json_encode($nextAssignee));
            }
            $postData = [
                'id'=> $user->id,
                'state' => 0,
                'assign_type' => 3,
                'url_code' => ""
            ];
            eCDocumentAssignee::find($user->id)->update($postData);
            eCDocumentSignature::where('assign_id', $user->id)
                ->update([
                    "assign_id" => $nextAssignee->id
                ]);
            if ($doc->is_order_approval == 0) {
                $doc->current_assignee_id = $nextAssignee->id;
            }
            $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $nextAssignee->full_name, $user->full_name, $user->email);
            $doc->save();
            DB::commit();

            $sendNextAssigneeParams = $this->documentHandlingService->getExts($id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
            if ($nextAssignee->assign_type == AssigneeType::SIGN) {
                if($doc->parent_id != -1){
                    $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendNextAssigneeParams);
                }else{
                    $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendNextAssigneeParams);
                }
            } else {
                if($doc->parent_id != -1){
                    $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::APPROVAL_REQUEST_ADDENDUM, $sendNextAssigneeParams);
                }else{
                    $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::APPROVAL_REQUEST_DOCUMENT, $sendNextAssigneeParams);
                }
            }

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function addApprovalAssignee($request)
    {
        $user = auth('assign')->user();
        $id = $request->docId;
        if ($id != $user->document_id) {
            throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }
        $doc = eCDocuments::find($id);
        if (!$doc) {
            throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        }

        if ($doc->is_order_approval == 0) {
            if ($doc->current_assignee_id != $user->id) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        } else {
            if (
                !($user->assign_type == AssigneeType::APPROVAL && $doc->document_state == DocumentState::WAIT_APPROVAL)
            ) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        }
        DB::beginTransaction();
        try {
            $password = Common::randomString(6);
            $urlCode = Common::randomString(10) . "-" . time();

            $nextAssignee = new eCDocumentAssignee();
            $nextAssignee->company_id = $user->company_id;
            $nextAssignee->full_name = $request->name;
            $nextAssignee->email = Str::lower($request->email);
            $nextAssignee->phone = $request->phone;
            $nextAssignee->message = $request->message;
            $nextAssignee->noti_type = 1;
            $nextAssignee->document_id = $user->document_id;
            $nextAssignee->partner_id = $user->partner_id;
            $nextAssignee->assign_type = $user->assign_type;
            //$nextAssignee->sign_method = $user->sign_method;
            $nextAssignee->password = Hash::make($password);
            $nextAssignee->url_code = $urlCode;
            $nextAssignee->save();
            if($doc->parent_id != -1){
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'ADD_ASSIGNEE_ADDENDUM', $doc->code, json_encode($nextAssignee));
            }else{
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION, $user, HistoryActionGroup::DOCUMENT_ACTION, 'ec_document_assignees', 'ADD_ASSIGNEE_DOCUMENT', $doc->code, json_encode($nextAssignee));
            }
            $this->documentHandlingService->insertDocumentLog($id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $nextAssignee->full_name . " thành công", $user->full_name, $user->email);
            DB::commit();

            if($doc->is_order_approval == 1){
                $sendNextAssigneeParams = $this->documentHandlingService->getExts($id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
                $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $id, NotificationType::APPROVAL_REQUEST_DOCUMENT, $sendNextAssigneeParams);
            }


            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function denyDocument($id, $rejectContent)
    {
        $user = auth('assign')->user();
        if ($id != $user->document_id) {
            throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }

        $doc = eCDocuments::find($id);

        if (!$doc || ($doc->document_state != DocumentState::WAIT_APPROVAL && $doc->document_state != DocumentState::WAIT_SIGNING)) {
            throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        }
        if ($doc->is_order_approval == 0) {
            if ($doc->current_assignee_id != $user->id) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        } else {
            if (!($user->assign_type == AssigneeType::APPROVAL && $doc->document_state == DocumentState::WAIT_APPROVAL) &&
                !($user->assign_type == AssigneeType::SIGN && $doc->document_state == DocumentState::WAIT_SIGNING)) {
                throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
            }
        }

        DB::beginTransaction();
        try {
            $submitTime = date('Y-m-d H:i:s');
            $postData = [
                'id'=> $user->id,
                'state' => 3, //da tu choi
                'reason' => $rejectContent,
                'submit_time' => $submitTime
            ];
            $raw_log = [
                'assign' => $user->full_name,
                'assign_email' => $user->email,
                'reason' => $rejectContent,
                'submit_time' => $submitTime
            ];
            eCDocumentAssignee::find($user->id)->update($postData);
            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($id, $user->full_name, $submitTime, $rejectContent, "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($id);
            if($doc->parent_id != -1){
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::DENY_ADDENDUM, $sendBeforeAssigneeParams);
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'DENY_ADDENDUM', $doc->code, json_encode($raw_log));
            }else{
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $id, NotificationType::DENY_DOCUMENT, $sendBeforeAssigneeParams);
                $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'DENY_DOCUMENT', $doc->code, json_encode($raw_log));
            }
            $doc->document_state = DocumentState::DENY;
            $doc->current_assignee_id = NULL;

            $doc->save();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    function hashDoc($docId, $pubKey) {
        $user = auth('assign')->user();
        $url = Common::getConverterServer() . '/api/v1/document/hash';
        $data = array(
            "document_id" => $docId,
            "ca_pub" => $pubKey,
            "assign_id" => $user->id

        );
        $response = $this->documentHandlingService->sendBackendServer($url, $data);
        Log::info($response);
        if ($response["status"] != true) {
            throw new eCBusinessException("SERVER.PROCESSING_ERROR");
        }
        return $response;
    }

    private function checkBeforeSign ($id)
    {
        $user = auth('assign')->user();
        if ($id != $user->document_id) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        $doc = eCDocuments::find($id);
        if (!$doc || $doc->document_state != DocumentState::WAIT_SIGNING)  throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if ($doc->is_order_approval == 0) {
            if ($doc->current_assignee_id != $user->id)  throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        } else {
            if (!($user->assign_type == AssigneeType::SIGN && $doc->document_state == DocumentState::WAIT_SIGNING)) throw new eCBusinessException("SERVER.NOT_PERMISSION_ACTION");
        }
        return $doc;
    }
    public function signDocument($id, $ip, $ca, $pubca) {
        $user = auth('assign')->user();

        $doc = $this->checkBeforeSign($id);

        $this->signDocumentHandling($doc, $user, 0, $ip, $ca, $pubca);
    }

    public function signOtpDocument($id, $ip, $otp) {
        $user = auth('assign')->user();

        $doc = $this->checkBeforeSign($id);

        if ($user->otp != $otp) {
            throw new eCBusinessException("SERVER.NOT_MATCH_OTP");
        }

        $this->signDocumentHandling($doc, $user, 1, $ip);
    }

    function signKycDocument($request) {
        $ip = $request->ip();
        $user = auth('assign')->user();
        $id = $request->docId;

        $doc = $this->checkBeforeSign($id);

        if ($user->national_id != $request->id) {
            throw new eCBusinessException("DOCUMENT.ERR_NOT_MATCH_NATIONAL_ID");
        }

        if ($request->sim < 70) {
            throw new eCBusinessException("DOCUMENT.ERR_NOT_MATCH_FACE");
        }
        $postData = [
            'assign_id'=> $user->id,
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
        eCDocumentSignatureKyc::where('assign_id', $user->id)->update($postData);
        $this->signDocumentHandling($doc, $user, 2, $ip);
    }

    public function signMySignDocument($request)
    {
        $ip = $request->ip();
        $user = auth('assign')->user();
        $id = $request->docId;

        $doc = $this->checkBeforeSign($id);
        $postData = [
            'user_id' => $request->user_id,
            'credential_id' => $request->credential_id
        ];
        eCDocumentAssignee::find($user->id)->update($postData);

        $this->signDocumentHandling($doc, $user, 3, $ip);
    }

    function signDocumentHandling($doc, $user, $sign_type, $ip, $ca = "", $pubca = "") {
        $creator = eCUser::find($doc->created_by);

        DB::beginTransaction();
        try {
            $assignType = -1;
            if ($doc->document_state == DocumentState::WAIT_APPROVAL) {
                $assignType = AssigneeType::APPROVAL;
            } else if ($doc->document_state == DocumentState::WAIT_SIGNING) {
                $assignType = AssigneeType::SIGN;
            }
            $currentAssignee = eCDocumentAssignee::with('partner')
                ->where('document_id', $doc->id)
                ->where('email', $user->email)
                ->whereIn('state', [0, 1])
                ->where('assign_type', $assignType)
                ->first();
            $submitTime = date('Y-m-d H:i:s');
            $postData = [
                'id'=> $user->id,
//                'state' => 2, //da giao ket
                'submit_time' => $submitTime
            ];
            $currentAssignee = eCDocumentAssignee::find($user->id);
            $currentAssignee->update($postData);
            DB::commit();
            if($doc->parent_id != -1){
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'UPDATE_ASSIGNEE', 'Cập nhật người giao kết phụ lục mã '.$doc->code, json_encode($postData));
            } else {
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'UPDATE_ASSIGNEE', 'Cập nhật người giao kết tài liệu mã '.$doc->code, json_encode($postData));
            };
            $nextAssignee = $this->documentHandlingService->getNextAssignee($doc->id, $doc->company_id);

            $url = Common::getConverterServer() . '/api/v1/document/sign';
            $data = array(
                "assign_id" => $user->id,
                "document_id" => $doc->id,
                "sign_type" => $sign_type,
                "sign_action" => $nextAssignee ? 0 : 1,
                "ca" => $ca,
                "ca_pub" => $pubca,
                "sign_company" => $currentAssignee->partner->organisation_type == 3 ? 0 : 1
            );
            Log::info($data);
            if($sign_type == 3) {
                $mergeResponse = $this->documentHandlingService->signMySign($url, $data, $ip);
            } else {
                $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            }
            if ($mergeResponse["status"] != true) {
                eCDocumentAssignee::find($user->id)
                ->update([
                    'state' => 0,
                    'submit_time' => null
                ]);
                DB::commit();
                if($sign_type == 3) {
                    $mergeResponse = $this->documentHandlingService->throwMessage($mergeResponse["data"]);
                    throw new eCBusinessException($mergeResponse);
                }
                throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
            }


            //TODO: gui email thong bao da phe duyet

            $sendBeforeAssigneeParams = $this->documentHandlingService->getExts($doc->id, $user->full_name, $submitTime, "", "", "", "", "", "", "");
            $lstAssignee = $this->documentHandlingService->getBeforeAssignees($doc->id);
            if($doc->parent_id != -1){
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $doc->id, NotificationType::AGREE_SIGN_ADDENDUM, $sendBeforeAssigneeParams);
            }else{
                $this->documentHandlingService->sendNotificationApi($lstAssignee, $doc->id, NotificationType::AGREE_SIGN_DOCUMENT, $sendBeforeAssigneeParams);
            }

//            $this->documentHandlingService->insertDocumentLog($doc->id, DocumentLogStatus::AGREE_APPROVAL, "Thực hiện ký số tài liệu thành công", $user->full_name, $user->email);

            if (!$nextAssignee) {
                $doc->current_assignee_id = NULL;
                if ($doc->is_verify_content) {
//                    $doc->document_state = DocumentState::NOT_AUTHORIZE;
                } else {
                    $doc->document_state = DocumentState::COMPLETE;
                    $doc->finished_date = date('Y-m-d H:i:s');
                    $price = $this->documentHandlingService->getFeeTurnOver($doc);
                    $doc->price = $price ? $price : 1000;
                    if($doc->expired_type == 2){
                        $doc->doc_expired_date = date('Y-m-d H:i:s',strtotime('+'.$doc->expired_month.' month',strtotime($doc->finished_date)));
                    }
                    if($doc->parent_id != -1){
                        $parent_doc = eCDocuments::find($doc->parent_id);
                        if($doc->addendum_type == 1){
                                if($doc->expired_type == 0){
                                    $parent_doc->update([
                                        'expired_type' => 0,
                                        'doc_expired_date'=> null
                                    ]);
                                } else if ($doc->expired_type == 1) {
                                    $parent_doc->update([
                                        'expired_type' => 1,
                                        'doc_expired_date' => $doc->doc_expired_date
                                    ]);
                                } else if ($doc->expired_type == 2) {
                                    $parent_doc->update([
                                        'expired_type' => 1,
                                        'doc_expired_date' => date('Y-m-d H:i:s',strtotime('+'.$doc->expired_month.' month',strtotime($parent_doc->doc_expired_date)))
                                    ]);
                                }
                        }
                        if($doc->addendum_type == 2){
                            $parent_doc->update([
                                        'document_state' => DocumentState::DROP,
                                    ]);
                        }
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'COMPLETE_ADDENDUM', $doc->code, json_encode($doc));
                    } else {
                        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'COMPLETE_DOCUMENT', $doc->code, json_encode($doc));
                    }
                    DB::commit();


                    //gui mail cho nguoi luu tru tai lieu
//                    $storagers = DB::table('ec_document_assignees')
//                        ->where('document_id', $doc->id)
//                        ->where('assign_type', 3)
//                        ->where('status', 1)
//                        ->select(["id", "message"])
//                        ->get();
//                    foreach ($storagers as $storager) {
//                        $password = Common::randomString(6);
//                        $urlCode = Common::randomString(10) . "-" . time();
//                        DB::table('ec_document_assignees')
//                            ->where('id', $storager->id)
//                            ->update([
//                                "password" => Hash::make($password),
//                                "url_code" => $urlCode
//                            ]);
//                        $sendStorageParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $storager->message);
//                        $this->documentHandlingService->sendNotificationApi([$storager->id], $doc->id, NotificationType::COMPLETE_DOCUMENT, $sendStorageParams);
//                    }
                }
            } else {
                if ($doc->is_order_approval == 0) {
                    if ($nextAssignee->assign_type == AssigneeType::SIGN && $nextAssignee->is_auto_sign == 1) {
                        $doc->current_assignee_id = $nextAssignee->id;
                        $doc->save();
                        DB::commit();
                        $this->signDocumentHandling($doc, $user, 2, $ip);
                    } else {
                        $password = Common::randomString(6);
                        $urlCode = Common::randomString(10) . "-" . time();
                        if ($nextAssignee->assign_type == 1 || $nextAssignee->assign_type == 2) {
                            eCDocumentAssignee::find($nextAssignee->id)
                                ->update([
                                    "password" => Hash::make($password),
                                    "url_code" => $urlCode
                                ]);
                        }
                        $sendNextAssigneeParams = $this->documentHandlingService->getExts($doc->id, "", "", "", $password, $urlCode, "", "", "", $nextAssignee->message);
                        if($doc->parent_id != -1){
                            $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $doc->id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendNextAssigneeParams);
                        }else{
                            $this->documentHandlingService->sendNotificationApi([$nextAssignee->id], $doc->id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendNextAssigneeParams);
                        }
                        $doc->current_assignee_id = $nextAssignee->id;
                        $this->documentHandlingService->insertDocumentLog($doc->id, DocumentLogStatus::SEND_EMAIL, "SEND_MAIL_SUCCESS/" . $nextAssignee->full_name, $doc->created_by == -1 ? "" : $creator->name, $doc->created_by == -1 ? "" : $creator->email);
                    }
                }
            }

            $doc->save();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function verifyOcr($request) {
        $user = auth('assign')->user();
        $id = $request->docId;
        $doc = $this->checkBeforeSign($id);
        $type = $request->type;
        $image = $request->image;
        try {
            $assigneeSignature = eCDocumentSignatureKyc::where('assign_id', $user->id)->first();
            $upload_path = $doc->document_type == DocumentType::INTERNAL ? '/internal/' : '/commerce/' ;
            $path = $this->storageHelper->uploadBase64File($image, $upload_path . $id . '/');
            $path = $this->imageHelper->resizeThumb($path);
            $postData = array();
            if ($type == 1) {
                $postData["front_image_url"] = $path;
            } else if ($type == 2) {
                $postData["back_image_url"] = $path;
            } else if ($type == 3) {
                $postData["face_image_url"] = $path;
            }
            if ($assigneeSignature) {
                eCDocumentSignatureKyc::where('assign_id', $user->id)
                    ->update($postData);
            } else {
                $postData["assign_id"] = $user->id;
                eCDocumentSignatureKyc::create($postData);
            }

            $res = $this->documentHandlingService->sendOcrApi($user->id, $type);
            $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_signature_kyc', 'VERIFY_OCR', 'Xác thực Ocr', json_encode($postData));
            return $res;
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function sendOtp($id) {
        $user = auth('assign')->user();

        $doc = $this->checkBeforeSign($id);

        $otpCode = Common::randomString(6, true);
        $postData = [
            'id'=> $user->id,
            'otp' => $otpCode
        ];
        eCDocumentAssignee::find($user->id)->update($postData);
        if($doc->parent_id != -1){
            $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'CREATE_OTP_ADDENDUM', $doc->code, json_encode($postData));
        }else{
            $this->actionHistoryService->SetActivity(HistoryActionType::REMOTE_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_document_assignees', 'CREATE_OTP_DOCUMENT', $doc->code, json_encode($postData));
        }

        $sendOtpParams = $this->documentHandlingService->getExts($doc->id, "", "", "", "", "", "", "", $otpCode, "");
        $this->documentHandlingService->sendNotificationApi([$user->id], $doc->id, NotificationType::SEND_OTP_DOCUMENT, $sendOtpParams);
        return true;
    }

    public function initGuideSetting()
    {
        $user = auth('assign')->user();
        $lstTutorial = eCDocumentTutorial::all();
        return array('lstTutorial' => $lstTutorial);
    }

    public function searchGuide($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = auth('assign')->user();
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
            $r->files = DB::select("SELECT d.* FROM ec_tutorial_document_resources as d JOIN ec_tutorial_documents as et ON d.document_tutorial_id = et.id WHERE et.id = ? ", array($r->id));
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function getDocumentDetail($id)
    {
        $user = auth('assign')->user();
        $service = eCDocumentTutorial::find($id);
        if (!$service) throw new eCBusinessException('SERVER.NOT_EXISTED_DOCUMENT');

        $lstDetail = eCDocumentTutorialResources::where('document_tutorial_id', $id)->get();

        return array("tutorial" => $service, "lstDetail" => $lstDetail);
    }

    public function getGuideDocument($id)
    {
        $user = auth('assign')->user();
        $doc = eCDocumentTutorial::with('resource')->find($id);
        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
        return $this->storageHelper->downloadFile($doc->resource->file_path_raw);
    }

    public function initGuideVideoSetting()
    {
        $user = auth('assign')->user();
        $lstGuideVideo = eCGuideVideo::all();
        return array('lstGuideVideo' => $lstGuideVideo);
    }

    public function searchGuideVideo($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = auth('assign')->user();
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


    public function getGuideVideoDetail($id)
    {
        $user = auth('assign')->user();
        $service = eCGuideVideo::find($id);
        if (!$service)  throw new eCBusinessException('SERVER.NOT_EXISTED_VIDEO');
        return array("guideVideo" => $service);
    }

    public function getListCts($user_id)
    {
        $user = auth('assign')->user();

        $url = Common::getConverterServer() . '/api/v1/get-list-credentials';
        $data = array(
            "user_id" => $user_id
        );
        $response = $this->documentHandlingService->sendBackendServer($url, $data);
        Log::info($response);
        if ($response["status"] != true) {
            $response = $this->documentHandlingService->throwMessage($response["data"]);
            throw new eCBusinessException($response);
        }
        return $response;
    }


    public function registerCTS($request)
    {
        $user = auth('assign')->user();
        $ip = $request->ip();
        $id = $request->docId;

        $doc = $this->checkBeforeSign($id);

        if ($user->national_id != $request->id) {
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
            eCDocumentSignatureKyc::where('assign_id', $user->id)->update($postData);

            $url = Common::getConverterServer() . '/api/v1/register-cts';
            $data = array(
                "assigneeId" => $user->id
            );
            $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            Log::info($mergeResponse);
            if ($mergeResponse["status"] != true) {
                throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
            }
            DB::commit();

            $data = eCDocumentHsm::where("assignee_id", $user->id)->first();
            return array('data' => $data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function signICA($request)
    {
        $user = auth('assign')->user();
        $ip = $request->ip();

        $id = $request->docId;

        $doc = $this->checkBeforeSign($id);

        $this->signDocumentHandling($doc, $user, 5, $ip);
    }
}
