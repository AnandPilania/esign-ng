<?php


namespace Customer\Services;


use Core\Helpers\Common;
use Core\Helpers\DocumentState;
use Core\Models\eCDocumentGroups;
use Core\Models\eCDocumentResourcesEx;
use Core\Models\eCDocuments;
use Core\Models\eCDocumentSampleInfo;
use Core\Models\eCDocumentSampleResources;
use Core\Models\eCDocumentTypes;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCDocumentHandlingService;
use Customer\Services\Shared\eCPermissionService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Core\Helpers\DocumentType;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Helpers\StorageHelper;
use Core\Models\eCDocumentSample;
use Core\Services\ActionHistoryService;

class eCDocumentSampleService
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
        $this->permissionService = $permissionService;
        $this->storageHelper = $storageHelper;
        $this->documentHandlingService = $documentHandlingService;
        $this->actionHistoryService = $actionHistoryService;
    }

    public function initDocumentSampleSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $lstDocumentGroup = eCDocumentGroups::where('status', 1)->get();
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)->where('status', 1)->get();
        return array('permission' => $permission, 'lstDocumentType' => $lstDocumentType, 'lstDocumentGroup' => $lstDocumentGroup);
    }

    public function searchDocumentSample($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $arr = array();
        $str = 'SELECT distinct(d.id), d.company_id, d.document_type_id, d.document_type, d.name, d.description, d.expired_type, d.expired_month, d.created_by, d.created_at, d.updated_at, d.updated_by, d.is_encrypt, d.save_password, t.name as document_type_name FROM ec_s_document_samples d JOIN ec_m_document_types t ON d.document_type = t.id';
        $strCount = 'SELECT count(*) as cnt FROM ec_s_document_samples d';
        $str .= " WHERE d.company_id = ? AND d.delete_flag = 0 ";
        $strCount .= " WHERE d.company_id = ? AND d.delete_flag = 0 ";
        array_push($arr, $user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ?)';
            $strCount .= ' AND (d.name LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_type"] != -1) {
            $str .= ' AND d.document_type = ?';
            $strCount .= ' AND d.document_type = ?';
            array_push($arr, $searchData["document_type"]);
        }

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND d.document_type_id = ?';
            $strCount .= ' AND d.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        $str .= " ORDER BY d." . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        foreach ($res as $r){
            $r->save_password = $r->is_encrypt && $r->save_password ? $r->save_password : "";
        }

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function getFileSampleDocument($id){
        $user = Auth::user();
        $resources = eCDocumentSampleResources::where('document_sample_id', $id)->get();
        if (!$resources) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        return array('data' => $resources);
    }

    public function insertDocumentSample($postData, $files, $ip)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'document_type_id' => 'required|exists:s_document_types,id',
            'document_type' => 'required|in:' . DocumentType::INTERNAL . ',' . DocumentType::COMMERCE,
            'name' => 'required|max:255',
            'expired_month' => 'nullable|max:4',
            'expired_type' => 'required|max:4',
            'description' => 'nullable|max:1024',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) throw new eCBusinessException('SERVER.INVALID_INPUT');

        if(gettype($files) != gettype(array()) || count($files) <= 0)throw new eCBusinessException('DOCUMENT_SAMPLE.NOT_UPLOAD_FILE');

        $exists_name = DB::table('ec_s_document_samples')->where('document_type_id', $postData['document_type_id'])->where('name', $postData['name'])->get();
        if(count($exists_name) > 0)throw new eCBusinessException('DOCUMENT_SAMPLE.EXIST_NAME');

        $postData["company_id"] = $user->company_id;
        $postData["created_by"] = $user->id;
        $postData["updated_by"] = $user->id;

        DB::beginTransaction();
        try {
            $sample = eCDocumentSample::create($postData);
            $dataFile = array();
            foreach($files as $file){
                $fileData = array();
                $fileData["file_name_raw"] = $file["name"];
                $fileData["file_type_raw"] = $file["extension"];
                $fileData["file_size_raw"] = $file["size_raw"];
                $fileData["file_path_raw"] = $file["path"];
                $fileData["file_id"] = $file["file_id"];
                $fileData["created_by"] = $user->id;
                $fileData["updated_by"] = $user->id;
                array_push($dataFile, $fileData);
            }
            $sample->resources()->createMany($dataFile);

            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_s_document_sample', 'INSERT_DOCUMENT_SAMPLE',$postData['name'], json_encode($postData));
            DB::commit();
            $url = Common::getConverterServer() . '/api/v1/document/merge/sample';
            $data = array(
                "sample_id" => $sample->id,
                "password" => $postData["save_password"] ? $postData["save_password"] : Common::getConverterPassword()
            );
            $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
            Log::info($mergeResponse);
            if ($mergeResponse["status"] != true) throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
        } catch(Exception $e){
            DB::rollback();
            throw $e;
        }
        return true;
    }

    public function updateDocumentSample($id, $postData, $files, $ip)
    {
        $user = Auth::user();

        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $rules = [
            'document_type_id' => 'required|exists:s_document_types,id',
            'document_type' => 'required|in:' . DocumentType::INTERNAL . ',' . DocumentType::COMMERCE,
            'name' => 'required|max:255',
            'expired_month' => 'nullable|max:4',
            'expired_type' => 'required|max:4',
            'description' => 'nullable|max:1024',
        ];

        $validator = Validator::make($postData, $rules);
        if ($validator->fails())  throw new eCBusinessException('SERVER.INVALID_INPUT');
        $document_sample = eCDocumentSample::find($id);
        if (!$document_sample) throw new eCBusinessException('SERVER.NOT_EXISTED_DOCUMENT');

        if ($document_sample->company_id != $user->company_id) throw new eCBusinessException('SERVER.NOT_PERMISSION_ACTION');

        if(gettype($files) != gettype(array()) || count($files) <= 0) throw new eCBusinessException('DOCUMENT_SAMPLE.NOT_UPLOAD_FILE');

        DB::beginTransaction();
        try {
            $document_sample->update($postData);

            $oldFiles = $document_sample->resources()->get();
            $oldPath = array();
            $removeOldFile =  false;
            if(count($oldFiles) != count($files)){
                $removeOldFile = true;
            } else {
                foreach ($oldFiles as $oldFile){
                    array_push($oldPath, $oldFile->file_path_raw);
                }
                foreach ($files as $file){
                    if(!in_array($file["path"], $oldPath) ){
                    $removeOldFile = true;
                    }
                }
            }
            if($removeOldFile){
                $document_sample->resources()->delete();
                $document_sample->sample_info()->delete();
                $dataFile = array();
                foreach($files as $file){
                    $fileData = array();
                    $fileData["file_name_raw"] = $file["name"];
                    $fileData["file_type_raw"] = $file["extension"];
                    $fileData["file_size_raw"] = $file["size_raw"];
                    $fileData["file_path_raw"] = $file["path"];
                    $fileData["file_id"] = $file["file_id"];
                    $fileData["created_by"] = $user->id;
                    $fileData["updated_by"] = $user->id;
                    array_push($dataFile, $fileData);
                }
                $eCDocumentSample->resources()->createMany($dataFile);
                DB::commit();
                $url = Common::getConverterServer() . '/api/v1/document/merge/sample';
                $data = array(
                    "sample_id" => $eCDocumentSample->id,
                    "password" => $postData["save_password"] ? $postData["save_password"] : Common::getConverterPassword()
                );
                $mergeResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
                Log::info($mergeResponse);
                if ($mergeResponse["status"] != true) {
                    throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
                }
            }
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_s_document_sample', 'UPDATE_DOCUMENT_SAMPLE', 'Cập nhật tài liệu mẫu' .$document_sample->name, json_encode($postData));
            DB::commit();
            } catch(Exception $e){
            DB::rollback();
            throw $e;
        }

        return true;
    }

    public function deleteDocumentSampleSetting($ids = [])
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $company_id = $user->company_id;

        $avail_document_samples = DB::table('ec_s_document_samples')->select('id','name')
            ->whereIn('id', $ids)->where('company_id', $company_id)->where('delete_flag', 0)->get()->toArray();
        $id = 1;
        foreach ($avail_document_samples as $p) {
            $avail_ids[] = $p->id;
            $avail_rm[$id++] = $p->name;
        }
        $count = count($avail_ids);
        if (!isset($avail_ids)) throw new eCBusinessException('DOCUMENT_SAMPLE.NOT_EXISTED');
        DB::beginTransaction();
        try {
            eCDocumentSample::whereIn('id', $avail_ids)
                ->update([
                    'delete_flag' => 1,
                    'updated_by' => $user->id,
                ]);
            eCDocumentSampleResources::whereIn('document_sample_id', $avail_ids)->delete();
                $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_s_document_sample', 'DELETE_DOCUMENT_SAMPLE', $count, json_encode($avail_rm));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }


    public function uploadFiles($request)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $document_type = $request->document_type;

        if (!$request->hasFile('files')) throw new eCBusinessException('SERVER.INVALID_INPUT');

        $files = $request->file("files");
        $this->documentHandlingService->validFileUpload($files, $user->company_id);

        try {
            $lstFileUploaded = array();
            foreach ($files as $file) {
                //TODO: Call API to convert and merge pdf
                $fileUploaded = array();
                if ($document_type == DocumentType::INTERNAL) {
                    $path = $this->storageHelper->uploadFile($file, '/document_sample/internal/', false);
                } else if ($document_type == DocumentType::COMMERCE) {
                    $path = $this->storageHelper->uploadFile($file, '/document_sample/commerce/', false);
                } else {
                    throw new eCBusinessException('SERVER.INVALID_INPUT');
                }
                $file_id = explode(".",explode("/", $path)[3])[0];
                $fileUploaded["file_id"] = $file_id;
                $fileUploaded["name"] = $file->getClientOriginalName();
                $fileUploaded["extension"] = $file->extension();
                $fileUploaded["size"] = $file->getSize();
                $fileUploaded["path"] = $path;
                array_push($lstFileUploaded, $fileUploaded);
            }
            return array('lstFileUploaded' => $lstFileUploaded);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function removeFile($file_id){
        try {
            if($file_id != null || $file_id != ""){
               eCDocumentSampleResources::where('file_id', $file_id)->delete();
            } else {
                throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDocumentDetail($id)
    {
        $user = Auth::user();
        $service = eCDocumentSample::find($id);
        if (!$service) throw new eCBusinessException('SERVER.NOT_EXISTED_DOCUMENT');

        $lstDetail = eCDocumentSampleResources::where('document_sample_id', $id)->get();
        $lstTexts = eCDocumentSampleInfo::where('document_sample_id', $id)->get();

        return array("sample" => $service, "lstDetail" => $lstDetail, 'lstTexts' => $lstTexts);
    }

    public function saveDetailSampleDocument($request) {
        $user = Auth::user();
        $ip = $request->ip();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "UTILITIES_DOCUMENT_SAMPLE", false, true, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();
        $id = $request->document_sample_id;
        $texts = $request->texts;

        $textDatas = array();
        foreach ($texts as $text) {
            array_push($textDatas, [
                "document_sample_id" => $id,
                "data_type" => $text["Type"],
                "content" => isset($text["content"]) ? $text["content"] : NULL,
                "description" => $text["description"],
                "is_required" => isset($text["is_required"]) ? $text["is_required"] : NULL,
                "is_editable" => isset($text["is_editable"]) ? $text["is_editable"] : NULL,
                "field_code" => isset($text["field_code"]) ? $text["field_code"] : NULL,
                "form_name" => isset($text["form_name"]) ? $text["form_name"] : NULL,
                "form_description" => isset($text["form_description"]) ? $text["form_description"] : NULL,
                "font_size" => isset($text["size"]) ? $text["size"] : NULL,
                "font_style" => isset($text["FontFamily"]) ? $text["FontFamily"] : NULL,
                "page_sign" => $text["Page"],
                "width_size" => $text["Width"],
                "height_size" => $text["Height"],
                "page_width" => $text["pageWidth"],
                "page_height" => $text["pageHeight"],
                "is_auto_sign" => isset($text["is_auto_sign"]) ? $text["is_auto_sign"] : NULL,
                "order_assignee" => isset($text["order_assignee"]) ? $text["order_assignee"] : NULL,
                "is_my_organisation" => isset($text["is_my_organisation"]) ? $text["is_my_organisation"] : NULL,
                "full_name" => isset($text["full_name"]) ? $text["full_name"] : NULL,
                "email" => isset($text["email"]) ? Str::lower($text["email"]) : NULL,
                "phone" => isset($text["phone"]) ? $text["phone"] : NULL,
                "national_id" => isset($text["national_id"]) ? $text["national_id"] : NULL,
                "sign_method" => isset($text["sign_method"]) ? $text["sign_method"] : NULL,
                "noti_type" => isset($text["noti_type"]) ? $text["noti_type"] : NULL,
                "image_signature" => isset($text["image_signature"]) ? $text["image_signature"] != "vcontract/assets/images/signature-icon.svg" ? $text["image_signature"]: NULL : NULL,
                "x" => $text["XAxis"],
                "y" => $text["YAxis"],
                "created_by" => $user->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_by' => $user->id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        DB::beginTransaction();
        try {
            $document_samples = eCDocumentSample::find($id);
            $document_samples->update([
                'updated_by' => $user->id
            ]);
            eCDocumentSampleInfo::where("document_sample_id", $id)->delete();
            eCDocumentSampleInfo::insert($textDatas);
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::DOCUMENT_ACTION,'ec_s_document_sample_info', 'INSERT_DOCUMENT_SAMPLE_INFO', $document_samples->name, json_encode($textDatas));
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getSampleDocument($id, $ip)
    {
        $user = Auth::user();
        try {
            // get giải mã file mã hóa
            $doc = eCDocumentSample::find($id);
            if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");
            if ($doc->is_encrypt) {
                $url = Common::getConverterServer() . '/api/v1/decryptSample-data';
                $data = array(
                    "sample_id" => $id,
                );
                $fileResponse = $this->documentHandlingService->sendConvertServer($url, $data, $ip);
                $fileResponse= json_decode($fileResponse['data']);
                return base64_decode($fileResponse->data);
            } else {
                return $this->storageHelper->downloadFile($doc->document_path_original);
            }
        } catch (Exception $e) {
            throw new eCBusinessException("SERVER.ERR_CONVERT_MERGE");
        }
    }
}
