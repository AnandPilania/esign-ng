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
use Core\Models\eCDocumentAssignee;
use Core\Models\eCDocumentLogs;
use Core\Models\eCDocumentPartners;
use Core\Models\eCDocumentResourcesEx;
use Core\Models\eCDocuments;
use Core\Models\eCDocumentSignature;
use Core\Models\eCDocumentSignatureKyc;
use Core\Models\eCDocumentTypes;
use Core\Models\eCSearcher;
use Core\Models\eCSearcherSignature;
use Core\Models\eCVerifyTransactionLogs;
use Core\Services\eContractBaseService;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCDocumentHandlingService;
use Customer\Services\Shared\eCPermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Core\Services\ActionHistoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class eCSearchService extends eContractBaseService
{
    private $permissionService;
    private $storageHelper;
    private $documentHandlingService;
    private $actionHistoryService;


    /**
     * eCUtilitiesService constructor.
     * @param eCPermissionService $permissionService
     * @param StorageHelper $storageHelper
     * @param eCDocumentHandlingService $documentHandlingService
     */
    public function __construct(eCPermissionService $permissionService, StorageHelper $storageHelper, eCDocumentHandlingService $documentHandlingService, ActionHistoryService $actionHistoryService)
    {
        parent::__construct(eCSearchService::class);
        $this->permissionService = $permissionService;
        $this->storageHelper = $storageHelper;
        $this->documentHandlingService = $documentHandlingService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function updateAccessToken($user)
    {
        return [
            'account' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'sex' => $user->sex,
            'status' => $user->status,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'token_type' => 'bearer',
            'token' => $user->token,
            'expires_in' => auth('search')->factory()->getTTL() * 60
        ];
    }

    public function getPassword($email)
    {
        DB::beginTransaction();
        try {
            $password = Common::randomString(10);
            eCSearcher::create([
                "name" => 'Unnamed',
                "email" => base64_encode($email),
                "password" => Hash::make($password),
                "is_first_login" => 1,
            ]);
            //TODO: sendmail

            $exts = array(
                "ma_tra_cuu" => $password
            );

            $this->documentHandlingService->sendNotificationSearch($email, "", NotificationType::SEND_PASSWORD, $exts, 1);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function forgetPassword($email)
    {

        DB::beginTransaction();
        try {
            $password = Common::randomString(10);
            eCSearcher::where('email', base64_encode($email))
            ->update([
                "password" => Hash::make($password),
            ]);
                //TODO: sendmail

            $exts = array(
                "ma_tra_cuu" => $password
            );

            $this->documentHandlingService->sendNotificationSearch($email, "", NotificationType::SEND_PASSWORD, $exts, 1);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getUserInfo($user)
    {
        return [
            'id' => $user->id,
            'address' => $user->address,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'sex' => $user->sex . '',
            'language' => $user->language,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'is_first_login' => $user->is_first_login
        ];
    }

    public function updateUserSignature($userSignature)
    {
        $user = auth('search')->user();

        $signature = eCSearcherSignature::where('searcher_id', $user->id)->first();
        if (!$signature) {
            $signature = new eCSearcherSignature();
            $signature->searcher_id = $user->id;
            $signature->image_signature = $userSignature;
            $signature->save();
        } else {
            DB::table("ec_user_signature")
                ->where('user_id', $user->id)
                ->update([
                    'image_signature' => $userSignature
                ]);
        }
        $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION, $user, HistoryActionGroup::USER_ACTION, 'ec_user_signature', 'UPDATE_USER_SIGNATURE', $user->name . ' cập nhật mẫu chữ ký cá nhân', json_encode($signature));


        return true;
    }

    public function updateFirstLogin($user)
    {
        eCSearcher::find($user->id)->update(['is_first_login' => false]);
        return true;
    }
    public function initDocumentListSetting()
    {
        $user = $user = auth('search')->user();;
        $lstDocumentType = eCDocumentTypes::where('status', 1)->get();

        return array('lstDocumentType' => $lstDocumentType);
    }

    public function searchDocument($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = auth('search')->user();
        $arr = array();
        $str = 'SELECT distinct(d.id), d.company_id, d.document_type_id, d.document_state, d.document_draft_state, d.status, d.sent_date, d.expired_date, d.finished_date, d.name, d.created_by, d.is_verify_content, d.code, dt.dc_style, d.branch_id FROM ec_documents d JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND d.code = ? AND a.status = 1 AND (a.state in (0,1,2,3) OR d.current_assignee_id = a.id) AND d.delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_documents d JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND d.code = ? AND a.status = 1 AND (a.state in (0,1,2,3) OR d.current_assignee_id = a.id) AND d.delete_flag = 0 ';
        array_push($arr, $user->email);
        array_push($arr,  $searchData["keyword"]);

        $str .= ' JOIN s_document_types dt ON d.document_type_id = dt.id WHERE NOT d.document_state = 1';
        $strCount .= ' JOIN s_document_types dt ON d.document_type_id = dt.id WHERE NOT d.document_state = 1';


        $str .= " ORDER BY d." . $sortQuery . ', id desc';

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function initViewDocument($id)
    {
        $user = auth('search')->user();
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

        if ($document->is_order_approval == 0) {
            if ($document->current_assignee_id) {
                $document->cur = eCDocumentAssignee::with('partner')->find($document->current_assignee_id);
            }
            if ($document->document_state == DocumentState::WAIT_SIGNING) {
                if ($document->cur && $document->cur->email == $user->email) {
                    $document->cur->signature = eCDocumentSignatureKyc::where('assign_id', $document->current_assignee_id)->first();
                    $document->signature_location = eCDocumentSignature::where('assign_id', $document->current_assignee_id)->get();
                }
            }
        } else {
            $assignee = $this->getCurrentAssigneeConcurrent($id, $document->document_state, $user);
            if ($assignee) {
                $document->cur = $assignee;
                if ($document->document_state == DocumentState::WAIT_SIGNING) {
                    $document->cur->signature = eCDocumentSignatureKyc::where('assign_id', $assignee->id)->first();
                    $document->signature_location = eCDocumentSignature::where('assign_id', $assignee->id)->get();
                }
            }
        }
        if ($rejectReason) {
            return array('document' => $document, 'reject_reason' => $rejectReason->reason);
        }
        return array('document' => $document);
    }

    public function getSignDocument($id)
    {
        $user = auth('search')->user();
        $doc = eCDocuments::find($id);
        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        $file = eCDocumentResourcesEx::where('document_id', $id)->where('status', 1)->first();
        return $this->storageHelper->downloadFile($file->document_path_sign);
    }

    public function getHistoryDocument($id)
    {
        $user = auth('search')->user();
        $doc = eCDocuments::find($id);
        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        $history = eCDocumentLogs::where('document_id', $id)->orderBy('id', 'asc')->get();
        return $history;
    }

    public function getHistoryTransactionDocument($id) {
        $user = auth('search')->user();
        $doc = eCDocuments::find($id);
        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        if (!$doc->transaction_id) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        $history = eCVerifyTransactionLogs::where('transaction_id', $doc->transaction_id)->get();
        return $history;
    }
}
