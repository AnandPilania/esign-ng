<?php


namespace Customer\Services;


use Core\Models\eCDocumentTutorialResources;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Exception;
use Core\Models\eCUser;
use Core\Models\eCRole;
use Core\Models\eCCompany;
use Core\Models\eCCompanyConsignee;
use Core\Models\eCCompanyRemoteSign;
use Core\Models\eCDocumentTutorial;
use Core\Models\eCGuideVideo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Core\Services\ActionHistoryService;
use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Helpers\StorageHelper;

class eCGuideService
{
    private $storageHelper;
    /**
     * eCUtilitiesService constructor.
     * @param $permissionService
     */

    public function __construct(StorageHelper $storageHelper)
    {
        $this->storageHelper = $storageHelper;
    }


    public function initGuideSetting()
    {
        $user = Auth::user();
        $lstTutorial = eCDocumentTutorial::all();
        return array('lstTutorial' => $lstTutorial);
    }

    public function searchGuide($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
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
            $r->files = DB::select("SELECT d.* FROM ec_tutorial_document_resources as d JOIN ec_tutorial_documents as et ON d.document_tutorial_id = et.id WHERE et.id = ? ",array($r->id) );
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function getDocumentDetail($id)
    {
        $user = Auth::user();
        $service = eCDocumentTutorial::find($id);
        if (!$service) throw new eCBusinessException('SERVER.NOT_EXISTED_DOCUMENT');

        $lstDetail = eCDocumentTutorialResources::where('document_tutorial_id', $id)->get();

        return array("tutorial" => $service, "lstDetail" => $lstDetail);
    }

    public function getGuideDocument($id)
    {
        $user = Auth::user();
        $doc = eCDocumentTutorial::with('resource')->find($id);

        if (!$doc) throw new eCBusinessException("SERVER.NOT_EXISTED_DOCUMENT");

        return $this->storageHelper->downloadFile($doc->resource->file_path_raw);
    }

    public function initGuideVideoSetting()
    {
        $user = Auth::user();
        $lstGuideVideo = eCGuideVideo::all();

        return array('lstGuideVideo' => $lstGuideVideo);
    }

    public function searchGuideVideo($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
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
        $user = Auth::user();
        $service = eCGuideService::find($id);
        if (!$service) throw new eCBusinessException('SERVER.NOT_EXISTED_VIDEO');
        return array("guideVideo" => $service);
    }
}
