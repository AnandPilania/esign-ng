<?php


namespace Customer\Services\Shared;

use Core\Helpers\AssigneeType;
use Core\Helpers\Common;
use Core\Helpers\DocumentState;
use Core\Helpers\NotificationType;
use Core\Models\eCCompanyConfig;
use Core\Models\eCDocumentAssignee;
use Core\Models\eCDocumentPartners;
use Core\Models\eCDocuments;
use Core\Helpers\GenerateContractCode;
use Core\Models\eCServiceConfig;
use Customer\Exceptions\eCBusinessException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Core\Models\eCDocumentLogs;
use Core\Models\ecDocumentConversations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpseclib\Crypt\Hash;

class eCDocumentHandlingService
{
    private $generateContractCode;

    /**
     * eCUtilitiesService constructor.
     * @param GenerateContractCode $generateContractCode
     */
    public function __construct(GenerateContractCode $generateContractCode)
    {
        $this->generateContractCode = $generateContractCode;
    }

    public function insertDocumentLog($docId, $action, $content, $action_by, $action_by_email, $isShow = 1, $prev = [], $next = []) {
        $log = new eCDocumentLogs();
        $log->document_id = $docId;
        $log->action = $action;
        $log->content = $content;
        $log->action_by = $action_by;
        $log->action_by_email = $action_by_email;
        $log->is_show = $isShow;
        $log->prev_value = json_encode($prev);
        $log->next_value = json_encode($next);
        $log->save();
    }

    public function getNextAssignee($docId, $companyId) {
        $nextAssignee = eCDocumentPartners::join('ec_document_assignees as a', 'a.partner_id', 'ec_document_partners.id')
            ->where('ec_document_partners.document_id', $docId)
            ->where('a.assign_type', '!=', 3) //nguoi xem tai lieu khong can xu ly van ban
            ->whereIn('a.state', ['0', '1'])
            ->orderBy('a.assign_type', 'asc')
            ->orderBy('ec_document_partners.order_assignee', 'asc')
            ->select('a.id', 'a.full_name', 'a.noti_type', 'a.assign_type', 'a.email', 'a.message', 'a.is_auto_sign', 'a.sign_method', 'a.state')
            ->first();
        return $nextAssignee;
    }

    public function getAllAssigneeByType($docId, $assignType) {
        $assignees = eCDocumentPartners::join('ec_document_assignees as a', 'a.partner_id', 'ec_document_partners.id')
            ->where('ec_document_partners.document_id', $docId)
            ->where('a.assign_type', '=', $assignType)
            ->whereIn('a.state', ['0', '1'])
            ->select('a.id', 'a.full_name', 'a.noti_type', 'a.assign_type', 'a.email', 'a.message', 'a.is_auto_sign', 'a.sign_method', 'a.state')
            ->get();
        return $assignees;
    }

    public function getBeforeAssignees($docId) {
        $beforeAssignees = eCDocumentAssignee::where('document_id', $docId)
            ->where('state', 2)
            ->where('status', 1)
            // ->groupBy('email')
            ->select(["id"])
            ->get();
        $lstAssignee = array();
        foreach ($beforeAssignees as $ass) {
            array_push($lstAssignee, $ass->id);
        }
        return $lstAssignee;
    }

    public function getExts($docId, $actor, $actionDate, $reason, $password, $urlCode, $oldDeadline, $newDateline, $otp, $message) {
        $params = array();
        $doc = eCDocuments::where('id', $docId)->first();
        $params["tai_lieu_cha"] ='';
        $params["loai_phu_luc"] = '';
        if($doc->parent_id != -1) {
            $parentDoc = eCDocuments::where('id', $doc->parent_id)->first();
            $params["tai_lieu_cha"] = $parentDoc->name;
            $params["loai_phu_luc"] = $this->checkAddendumType($doc->addendum_type);
        }
        $params["ten_tai_lieu"] = $doc->name;
        $params["han_tai_lieu"] = $doc->expired_date;
        $params["thoi_gian_hoan_thanh"] = $doc->finished_date;
        $params["ma_tra_cuu"] = $password;
        $params["trang_tra_cuu"] = Common::getSignUrl() . $urlCode;
        $params["nguoi_tu_choi"] = $params["nguoi_duyet"] = $params["nguoi_ky"] = $actor;
        $params["ngay_duyet"] = $params["ngay_ky"] = $params["ngay_tu_choi"] = $actionDate;
        $params["ly_do_tu_choi"] = $reason;
        $params["thoi_han_cu"] = $oldDeadline;
        $params["thoi_han_moi"] = $newDateline;
        $params["ma_otp"] = $otp;
        $params["loi_nhan"] = $message;

        return $params;
    }

    public function sendConvertServer($url, $data, $ip) {
        $header = array(
            'Content-Type: application/json',
            'SOURCE_IP: ' . $ip
        );
        return Common::httpPost($url, $data, $header);
    }

    public function signMySign($url, $data, $ip) {
        $header = array(
            'Content-Type: application/json',
            'SOURCE_IP: ' . $ip
        );
        return Common::httpPostMySign($url, $data, $header);
    }

    public function sendBackendServer($url, $data) {
        $header = array(
            'Content-Type: application/json'
        );
        return Common::httpPost($url, $data, $header);
    }

    public function sendNotificationApi($assignees, $docId, $template, $exts) {
        $url = Common::getConverterServer() . '/api/v1/notify';
        $data = array(
            "assignee_ids" => $assignees,
            "document_id" => $docId,
            "template_name" => $template,
            "exts" => $exts,
            "type" => -1
        );

        $header = array(
            'Content-Type: application/json'
        );
        Log::info($data);
        $sendRes = Common::httpPost($url, $data, $header);
        Log::info($sendRes);
    }

    public function sendNotificationApiAccount($assignees, $docId, $template, $exts) {
        $url = Common::getConverterServer() . '/api/v1/notify/account';
        $data = array(
            "assignee_ids" => $assignees,
            "document_id" => $docId,
            "template_name" => $template,
            "exts" => $exts,
            "type" => -1
        );

        $header = array(
            'Content-Type: application/json'
        );
        Log::info($data);
        $sendRes = Common::httpPost($url, $data, $header);
        Log::info($sendRes);
    }


    public function sendOcrApi($assigneeId, $type) {
        $url = Common::getConverterServer() . '/api/v1/orc/ekyc/verify';
        $data = array(
            "assignee_id" => $assigneeId,
            "type" => $type
        );

        $header = array(
            'Content-Type: application/json'
        );
        Log::info($data);
        $sendRes = Common::httpPost($url, $data, $header);
        Log::info($sendRes);
        return $sendRes;
    }

    public function resendNotification($convId) {
        $url = Common::getConverterServer() . '/api/v1/notify/re-notify';
        $data = array(
            "conversationId" => $convId
        );
        $header = array(
            'Content-Type: application/json'
        );
        Log::info($data);
        $sendRes = Common::httpPost($url, $data, $header);
        Log::info($sendRes);

    }

    public function getCode($code, $codeNoReset, $lastDoc, $documentType)
    {
        if (!$lastDoc) {
            $lastCode = 0;
        } else {
            $lastCode =  explode("/", explode("-", $lastDoc->code)[0])[0];
        }
        if($documentType->is_auto_reset == 0 ){
            if(strlen((string)($lastCode + 1)) > $documentType->dc_length) {
                throw new eCBusinessException(' Chỉ số sinh mã HĐ của loại HĐ đã vượt ngưỡng cho phép!');
            }
        }
        if($documentType->is_auto_reset == 0){
            $genCode = $this->generateContractCode->generatorCodeId($codeNoReset, $lastCode);
            $code = str_replace("{number}", '' . sprintf("%0" . $documentType->dc_length . 'd', $genCode), $code);
        } else {
            $genCode = $this->generateContractCode->generatorCodeId($code, $lastCode);
            if($genCode < 10){
                $code = str_replace("{number}", '0' . $genCode, $code);
            } else {
                $code = str_replace("{number}",  $genCode, $code);
            }
        }
        return $code;
    }

//    public function validateCode($code, $document_type_id ,$documentType)
//    {
//
//        $lastDoc = eCDocuments::where('document_type_id', $document_type_id)
//            ->where('parent_id', -1)
//            ->orderBy('id', 'desc')
//            ->select('code')
//            ->first();
//        if (!$lastDoc) {
//            $newCode =  $this->getCode($code, '', $documentType, 0);
//            $lastCode =  explode("/", explode("-", $newCode)[0]);
//        } else {
//            $lastCode =  explode("/", explode("-", $lastDoc->code)[0]);
//        }
//
//        if(count(explode("-", $code)) < 2){
//           return false;
//        }
//        $currentCode = explode("/",  explode("-", $code)[0]);
//        if(count($lastCode) != count($currentCode)){
//            return false;
//        }
//
//        $checkCode = [];
//        foreach($currentCode as $code) {
//            $checkCode['code'] = $code;
//            $test = $this->testCode($checkCode);
//            if(!$test){
//                return false;
//            }
//        }
//        if($currentCode[0] < 10 && strlen($currentCode[0]) < 2){
//            return false;
//        }
//        if($documentType->is_auto_reset == 0){
//            if(strlen($currentCode[0]) != strlen($lastCode[0])){
//                return false;
//            }
//        }
//        if(count($currentCode) == 2) {
//            if(strlen($currentCode[1]) != strlen($lastCode[1])) {
//                return false;
//            } else if ($currentCode[1] < $lastCode[1]) {
//                return false;
//            }
//        }
//        if(count($currentCode) == 3) {
//            if(strlen($currentCode[2]) == 2 && $currentCode[2] != date('y')) {
//                return false;
//            }
//            if(strlen($currentCode[2]) == 4 && $currentCode[2] != date('Y')) {
//                return false;
//            }
//            if(strlen($currentCode[2]) != strlen($lastCode[2])) {
//                return false;
//            }
//            if ($currentCode[1] < $lastCode[1] && $currentCode[2] == $lastCode[2] || $currentCode[1] > 12 ) {
//                return false;
//            }
//        }
//        return true;
//    }
//
//    public function testCode($checkCode)
//    {
//        $rules = [
//            'code' => 'required|regex:/^\+?\d+$/'
//        ];
//        $validator = Validator::make($checkCode, $rules);
//        if($validator->fails()){
//            return false;
//        } else {
//            return true;
//        }
//    }
    public function sendNotificationSearch($email, $phoneNumber, $template, $exts, $type) {
        $url = Common::getConverterServer() . '/api/v1/notify/account';
        $data = array(
            "email" => $email,
            "number_phone" => $phoneNumber,
            "template_name" => $template,
            "exts" => $exts,
            "type" => $type
        );

        $header = array(
            'Content-Type: application/json'
        );
        Log::info($data);
        $sendRes = Common::httpPost($url, $data, $header);
        Log::info($sendRes);
    }

    public function checkAddendumType($type)
    {
        switch ($type){
            case '0':
                return 'bổ sung';
            case '1':
                return 'gia hạn';
            case '2':
                return 'hủy bỏ';
            default :
                return false;
            }
    }

    public function throwMessage($res){
        $res = str_replace("\"", "", $res);
        $res= str_replace("}", "", $res);
        $res = str_replace("{", "", $res);
        $res = str_replace(" ", "", $res);
        $res = last(explode(":" ,$res));
        return $res;
    }

    public function sendNotifySign($assign, $doc, $sendParams) {
        if ($assign->assign_type == AssigneeType::APPROVAL) {
            if ($doc->parent_id != -1) {
                $this->sendNotificationApi([$assign->id], $doc->id, NotificationType::APPROVAL_REQUEST_ADDENDUM, $sendParams);
            } else {
                $this->sendNotificationApi([$assign->id], $doc->id, NotificationType::APPROVAL_REQUEST_DOCUMENT, $sendParams);
            }
            if ($doc->is_order_approval == 0) {
                $doc->document_state = DocumentState::WAIT_APPROVAL;
            }
        } else if ($assign->assign_type == AssigneeType::SIGN) {
            if ($doc->parent_id != -1) {
                $this->sendNotificationApi([$assign->id], $doc->id, NotificationType::SIGN_REQUEST_ADDENDUM, $sendParams);
            } else {
                $this->sendNotificationApi([$assign->id], $doc->id, NotificationType::SIGN_REQUEST_DOCUMENT, $sendParams);
            }

            if ($doc->is_order_approval == 0) {
                $doc->document_state = DocumentState::WAIT_SIGNING;
            }
        }
    }
    public function validFileUpload($files, $company_id )
    {
        $config = eCCompanyConfig::where('company_id', $company_id)->first();
        foreach ($files as $file) {
            if($file->extension() != 'docx' && $file->extension() != 'doc' && $file->extension() != 'pdf'){
                throw new eCBusinessException("DOCUMENT.INVALID_FILE_TYPE");
            }
            if($file->getSize() > $config->file_size_upload * 1024 * 1024){
                throw new eCBusinessException("DOCUMENT.ERR_OVERSIZE_UPLOAD");
            }
        }
    }
    public function getFeeTurnOver($doc)
    {
        $configService = eCServiceConfig::select('s_service_config_detail.from','s_service_config_detail.to','s_service_config_detail.fee' )
            ->join('ec_companies', 'ec_companies.service_id', '=', 's_service_config_detail.service_config_id')
            ->where('ec_companies.id',$doc->company_id)
            ->get();
        $oldTotal = eCDocuments::select(DB::raw('count(*) as total'))
            ->where('document_state', '>', 1)
            ->where('parent_id', -1)
            ->where('company_id',$doc->company_id)
            ->get();
        Log::info($oldTotal);
        $i = 0;
        while ($configService[$i]->to < $oldTotal[0]->total) {
            $i++;
        };

        return isset($configService[$i]) ? $configService[$i]->fee : null;
    }
}
