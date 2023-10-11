<?php


namespace Customer\Services;


use Core\Helpers\Common;
use Core\Helpers\DocumentHandler;
use Core\Helpers\DocumentState;
use Core\Helpers\DocumentStyle;
use Core\Helpers\DocumentType;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Helpers\NotificationType;
use Core\Models\eCDocumentGroups;
use Core\Models\eCDocuments;
use Core\Models\eCDocumentTypes;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCDocumentHandlingService;
use Customer\Services\Shared\eCPermissionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Core\Services\ActionHistoryService;

class eDocumentHandlerService
{
    private $permissionService;
    private $documentHandlingService;
    private $actionHistoryService;

    /**
     * eDocumentHandlerService constructor.
     * @param $permissionService
     */
    public function __construct(eCPermissionService $permissionService, eCDocumentHandlingService $documentHandlingService, ActionHistoryService $actionHistoryService)
    {
        $this->permissionService = $permissionService;
        $this->documentHandlingService = $documentHandlingService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function initDocumentList($document_group)
    {
        $user = Auth::user();

        switch ($document_group) {
            case DocumentHandler::NearExpire:
                $func = "DOCUMENT_HANDLER_NEAR_EXPIRE";
                break;
            case DocumentHandler::Expired:
                $func = "DOCUMENT_HANDLER_EXPIRE";
                break;
            case DocumentHandler::DENY:
                $func = "DOCUMENT_HANDLER_DENY";
                break;
            case DocumentHandler::DELETE:
                $func = "DOCUMENT_HANDLER_DELETE";
                break;
            case DocumentHandler::DocNearExpire:
                $func = "DOCUMENT_HANDLER_NEAR_DOC_EXPIRE";
                break;
            case DocumentHandler::DocExpired:
                $func = "DOCUMENT_HANDLER_DOC_EXPIRE";
                break;
            default:
                $func = "";
        }
        $permission = $this->permissionService->getPermission($user->role_id, $func);
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }

        //get list type (noi bo hay thuong mai)
        $lstDocumentGroup = eCDocumentGroups::where('status', 1)->get();
        if (!$lstDocumentGroup) {
            throw new eCBusinessException('SERVER.DOCUMENT_GROUP_NOT_EXISTED');
        }

        //lay ra item dau tien trong list type
        $firstDocumentGroupId = $lstDocumentGroup -> first() -> id;
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('document_group_id', $firstDocumentGroupId)
            ->where('status', 1)
            ->get();

        return array('permission' => $permission,'firstDocumentGroupId' => $firstDocumentGroupId ,'lstDocumentGroup' => $lstDocumentGroup, 'lstDocumentType' => $lstDocumentType);
    }

    public function changeDocumentGroup($groupId)
    {
        $user = Auth::user();
        $documentGroup = eCDocumentGroups::find($groupId);
        if (!$documentGroup) {
            throw new eCBusinessException('SERVER.DOCUMENT_GROUP_NOT_EXISTED');
        }

       $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id',$documentGroup ->id)
            ->get();

        return array('lstDocumentType' => $lstDocumentType);
    }

    public function searchDocumentList($searchData, $draw, $start, $limit, $sortQuery, $document_group)
    {
        $user = Auth::user();

        $time_config = DB::table("ec_s_config_params")
                ->where('company_id', $user->company_id)
                ->first();
        $time_near_expired_date = $time_config->near_expired_date;
        $time_near_doc_expired_date = $time_config->near_doc_expired_date;
        switch ($document_group) {
            case DocumentHandler::NearExpire:
                $func = "DOCUMENT_HANDLER_NEAR_EXPIRE";
                $checkExpired = true;
                $checkDocExpired = false;
                $overLapse = false;
                $documentStatus = [DocumentState::WAIT_APPROVAL, DocumentState::WAIT_SIGNING];
                $searchData["start_date"] = date('Y/m/d 00:00:00', time());
                $searchData["end_date"] = date('Y/m/d 23:59:59', strtotime("+" . $time_near_expired_date ." days"));
                break;
            case DocumentHandler::Expired:
                $func = "DOCUMENT_HANDLER_EXPIRE";
                $checkExpired = false;
                $checkDocExpired = false;
                $overLapse = false;
                $documentStatus = [DocumentState::OVERDUE];
                break;
            case DocumentHandler::DocNearExpire:
                $func = "DOCUMENT_HANDLER_NEAR_DOC_EXPIRE";
                $checkExpired = false;
                $checkDocExpired = true;
                $overLapse = false;
                $documentStatus = [DocumentState::COMPLETE];
                $searchData["start_date"] = date('Y/m/d 00:00:00', time());
                $searchData["end_date"] = date('Y/m/d 23:59:59', strtotime("+" . $time_near_doc_expired_date ." days"));
                break;
            case DocumentHandler::DocExpired:
                $func = "DOCUMENT_HANDLER_DOC_EXPIRE";
                $checkExpired = false;
                $checkDocExpired = false;
                $overLapse = true;
                $documentStatus = [DocumentState::OVERLAPSE];
                $searchData["start_date"] = date('Y/m/d 00:00:00', time());
                break;
            case DocumentHandler::DENY:
                $func = "DOCUMENT_HANDLER_DENY";
                $checkExpired = false;
                $checkDocExpired = false;
                $overLapse = false;
                $documentStatus = [DocumentState::DENY];
                break;
            case DocumentHandler::DELETE:
                $func = "DOCUMENT_HANDLER_DELETE";
                $checkExpired = false;
                $checkDocExpired = false;
                $overLapse = false;
                $documentStatus = [DocumentState::DROP];
                break;
            default:
                $func = "";
                $overLapse = false;
                $checkExpired = false;
                $checkDocExpired = false;
                $documentStatus = [];
        }
        $documentStatus = join(",",$documentStatus);

        $hasPermission = $this->permissionService->checkPermission($user->role_id, $func, false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $arr = array();
        $str = 'SELECT distinct(d.id), d.company_id, d.document_type_id, d.document_state, d.document_draft_state, d.status, d.sent_date, d.expired_date, d.finished_date, d.doc_expired_date, d.name, d.code, d.created_by, d.is_verify_content , dt.dc_style, d.updated_at, d.parent_id FROM ec_documents d ';
        $strCount = 'SELECT count(*) as cnt FROM ec_documents d';
        if ($user->is_personal) {
            $str .= ' JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND a.status = 1 AND (a.state in (1,2,3) OR d.current_assignee_id = a.id)';
            $strCount .= ' JOIN ec_document_assignees a ON d.id = a.document_id AND a.email= ? AND a.status = 1 AND (a.state in (1,2,3) OR d.current_assignee_id = a.id)';
            array_push($arr, $user->email);
        }

        $str .= ' JOIN s_document_types dt ON d.document_type_id = dt.id';
        $strCount .= ' JOIN s_document_types dt ON d.document_type_id = dt.id';
        $str .= " WHERE d.company_id = ? AND d.delete_flag = 0 and d.document_state in (".$documentStatus.")";
        $strCount .= " WHERE d.company_id = ? AND d.delete_flag = 0 AND d.document_state in (".$documentStatus.")";
        array_push($arr, $user->company_id);

        if (!$user->is_personal) {
            if ($user->branch_id && $user->branch_id != -1) {
                $str .= ' AND d.branch_id = ?';
                $strCount .= ' AND d.branch_id = ?';
                array_push($arr, $user->branch_id);
            }
        }

        if ($searchData["parent_id"] != -1) {
            $str .= ' AND d.parent_id != -1';
            $strCount .= ' AND d.parent_id != -1';
        } else {
            $str .= ' AND d.parent_id = ?';
            $strCount .= ' AND d.parent_id = ?';
            array_push($arr, $searchData["parent_id"]);
        }

        if ($searchData["dc_style"] != "-1")	{
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ?)';
            $strCount .= ' AND (d.name  LIKE ? OR d.code LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_group_id"] != -1) {
            $str .= ' AND d.document_type = ?';
            $strCount .= ' AND d.document_type = ?';
            array_push($arr, $searchData["document_group_id"]);
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND d.document_type_id = ?';
            $strCount .= ' AND d.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($checkExpired){
            if ($searchData["start_date"] && $searchData['end_date']) {
                $str .= ' AND d.expired_date >= ? AND d.expired_date <= ?';
                $strCount .= ' AND d.expired_date >= ? AND d.expired_date <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["end_date"]))));
            } else if ($searchData["start_date"] && !$searchData['end_date']) {
                $str .= ' AND d.expired_date >= ? ';
                $strCount .= ' AND d.expired_date >= ? ';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
            } else if (!$searchData["start_date"] && $searchData['end_date']) {
                $str .= ' AND d.expired_date <= ?';
                $strCount .= ' AND d.expired_date <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["end_date"]))));
            }
        }else if($checkDocExpired){
            if ($searchData["start_date"] && $searchData['end_date']) {
                $str .= ' AND d.doc_expired_date >= ? AND d.doc_expired_date <= ?';
                $strCount .= ' AND d.doc_expired_date >= ? AND d.doc_expired_date <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["end_date"]))));
            } else if ($searchData["start_date"] && !$searchData['end_date']) {
                $str .= ' AND d.doc_expired_date >= ? ';
                $strCount .= ' AND d.doc_expired_date >= ? ';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
            } else if (!$searchData["start_date"] && $searchData['end_date']) {
                $str .= ' AND d.doc_expired_date <= ?';
                $strCount .= ' AND d.doc_expired_date <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["end_date"]))));
            }
        }else if($overLapse){
            if ($searchData["start_date"]) {
                $str .= ' AND d.doc_expired_date <= ?';
                $strCount .= ' AND d.doc_expired_date <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
            }
        }else{
            if ($searchData["start_date"] && $searchData['end_date']) {
                $str .= ' AND d.updated_at >= ? AND d.updated_at <= ?';
                $strCount .= ' AND d.updated_at >= ? AND d.updated_at <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["end_date"]))));
            } else if ($searchData["start_date"] && !$searchData['end_date']) {
                $str .= ' AND d.updated_at >= ? ';
                $strCount .= ' AND d.updated_at >= ? ';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["start_date"]))));
            } else if (!$searchData["start_date"] && $searchData['end_date']) {
                $str .= ' AND d.updated_at <= ?';
                $strCount .= ' AND d.updated_at <= ?';
                array_push($arr, date('Y-m-d', strtotime(str_replace('/', '-', $searchData["end_date"]))));
            }
        }

        $str .= " ORDER BY d." . $sortQuery;
        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        Log::info('-----------'.$str);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        foreach($res as $r){
            if ($r->parent_id != -1){
                $r->parent_doc = DB::table('ec_documents')
                    ->where('id', $r->parent_id)
                    ->select('id', 'name')
                    ->first();
            }
        }

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, 'str' => $str);
    }

    //hop dong sap qua han
    public function renewDocumentListSetting($postData)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "DOCUMENT_HANDLER_NEAR_EXPIRE", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;
        $documentStatus = [DocumentState::WAIT_APPROVAL, DocumentState::WAIT_SIGNING, DocumentState::NOT_AUTHORIZE];

        $avail_documentList = eCDocuments::select('id','expired_date','code', 'parent_id')
            ->whereIn('id', $postData['lst_document_list'])
            ->whereIn('document_state', $documentStatus)
            ->orderByRaw(\DB::raw("FIELD(document_state, ".implode(",",$documentStatus).")"))
            ->where('company_id', $company_id)
            ->get();
        $expired_date = Carbon::createFromFormat('d/m/Y',$postData['expired_date'])->endOfDay()->format('Y-m-d H:i:s');

        foreach ($avail_documentList as $p) {
            $avail_ids[] = $p->id;
            $avail_rv[`doc_code: $p->code`] = `expired_date: $expired_date`;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.NOT_EXISTED_DOCUMENT');

        try {
            //update
            DB::beginTransaction();
            eCDocuments::whereIn('id', $avail_ids)
                ->update([
                    'expired_date' => $expired_date,
                    'remimder_type' => 0,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'RENEW_DOCUMENT_LIST', $count, json_encode($avail_rv));
            DB::commit();
            //send email
            foreach ($avail_documentList as $p) {
                $currentAssignee = $this->documentHandlingService->getNextAssignee($p->id, $user->company_id);
                $sendParams = $this->documentHandlingService->getExts($p->id, "", "", "", "", "", $p->expired_date,  Carbon::createFromFormat('d/m/Y',$postData['expired_date'])->endOfDay()->format('Y-m-d H:i:s'), "", $postData['email_content']);
                if($p->parent_id != -1){
                    $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $p->id, NotificationType::EXTEND_ADDENDUM, $sendParams);
                }else{
                    $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $p->id, NotificationType::EXTEND_DOCUMENT, $sendParams);
                }
            }

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    //hop dong qua han va hop dong bi tu choi
    public function deleteDocumentListSetting($ids = [], $document_group)
    {
        $user = Auth::user();

        switch ($document_group) {
            case DocumentHandler::Expired:
                $func = "DOCUMENT_HANDLER_EXPIRE";
                break;
            case DocumentHandler::DocExpired:
                $func = "DOCUMENT_HANDLER_EXPIRE";
                break;
            case DocumentHandler::DENY:
                $func = "DOCUMENT_HANDLER_DENY";
                break;
            default:
                $func = "";
        }

        $hasPermission = $this->permissionService->checkPermission($user->role_id, $func, false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;
        $documentStatus = [DocumentState::DENY, DocumentState::OVERDUE,DocumentState::OVERLAPSE];

        $avail_documentList = eCDocuments::select('id','code','parent_id')
            ->whereIn('id', $ids)
            ->whereIn('document_state', $documentStatus)
            ->orderByRaw(\DB::raw("FIELD(document_state, ".implode(",",$documentStatus).")"))
            ->where('company_id', $company_id)
            ->get();
        $i = 1;
        foreach ($avail_documentList as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$i++] = `doc_code: $p->code`;
        }
        $count = count($avail_rm);
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_HANDLE.EXPIRED_DOCUMENT_LIST.NOT_EXISTED_DOCUMENT');

        try {
            DB::beginTransaction();
            eCDocuments::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'DELETE_DOCUMENT', '    '. $count .' tài liệu', json_encode($avail_rm));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function sendDocumentListSetting($postData, $document_group)
    {
        $user = Auth::user();

        switch ($document_group) {
            case DocumentHandler::Expired:
                $func = "DOCUMENT_HANDLER_EXPIRE";
                break;
            case DocumentHandler::DENY:
                $func = "DOCUMENT_HANDLER_DENY";
                break;
            default:
                $func = "";
        }

        $hasPermission = $this->permissionService->checkPermission($user->role_id, $func, false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;
        $documentStatus = [DocumentState::DENY, DocumentState::OVERDUE];

        $avail_documentList = eCDocuments::select('id','expired_date','code','parent_id')
            ->whereIn('id', $postData['lst_document_list'])
            ->whereIn('document_state', $documentStatus)
            ->orderByRaw(\DB::raw("FIELD(document_state, ".implode(",",$documentStatus).")"))
            ->where('company_id', $company_id)
            ->get();
        $expired_date = Carbon::createFromFormat('d/m/Y',$postData['expired_date'])->endOfDay()->format('Y-m-d H:i:s');

        foreach ($avail_documentList as $p) {
            $avail_ids[] = $p->id;
            $avail_rv[`doc_code: $p->code`] = `expired_date: $expired_date`;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_HANDLE.EXPIRED_DOCUMENT_LIST.NOT_EXISTED_DOCUMENT');

        try {

            //update
            DB::beginTransaction();
            eCDocuments::whereIn('id', $avail_ids)
                ->update([
                    'document_state' => DocumentState::WAIT_APPROVAL,
                    'expired_date' => $expired_date,
                    'remimder_type' => 0,
                    'updated_by' => $user->id,
                ]);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'RESEND_DOCUMENT', $count, json_encode($avail_rv));
            DB::commit();
            //send email
            foreach ($avail_documentList as $p) {
                $currentAssignee = $this->documentHandlingService->getNextAssignee($p->id, $user->company_id);
                $sendParams = $this->documentHandlingService->getExts($p->id, "", "", "", "", "", $p->expired_date,  Carbon::createFromFormat('d/m/Y',$postData['expired_date'])->endOfDay()->format('Y-m-d H:i:s'), "", $postData['email_content']);
                if($p->parent_id != -1){
                    $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $p->id, NotificationType::EXTEND_ADDENDUM, $sendParams);
                }else{
                    $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $p->id, NotificationType::EXTEND_DOCUMENT, $sendParams);
                }
            }

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    //hop dong bi huy bo
    public function restoreMultiDocumentList($postData)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "DOCUMENT_HANDLER_DELETE", false, true, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }
        $company_id = $user->company_id;
        $avail_documentList = eCDocuments::select('id','code','parent_id')
            ->whereIn('id', $postData['lst_document_list'])
            ->where('document_state', DocumentState::DROP)
            ->where('company_id', $company_id)
            ->get();
        $i = 1;
        foreach ($avail_documentList as $p) {
            $avail_ids[] = $p->id;
            $avail_rt[$i++] = `Doc_code: $p->code`;
        }
        $count = count($avail_ids);
        Log::info($count);
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_HANDLE.DELETE_DOCUMENT_LIST.NOT_EXISTED_DOCUMENT');

        try {

            //update
            DB::beginTransaction();
            eCDocuments::whereIn('id', $avail_ids)
                ->update([
                    'document_state' => DocumentState::COMPLETE,
                    'updated_by' => $user->id,
                ]);
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_documents', 'RESIGN_DOCUMENT', $count, json_encode($avail_rt));

                DB::commit();

            //send email
            foreach ($avail_documentList as $p) {
                $currentAssignee = $this->documentHandlingService->getNextAssignee($p->id, $user->company_id);
                $sendParams = $this->documentHandlingService->getExts($p->id, "", "", "", "", "", "", "", "", $postData['email_content']);

                if($p->parent_id != -1){
                    $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $p->id, NotificationType::RESTORE_ADDENDUM, $sendParams);
                }else{
                    $this->documentHandlingService->sendNotificationApi([$currentAssignee->id], $p->id, NotificationType::RESTORE_DOCUMENT, $sendParams);
                }
            }

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
