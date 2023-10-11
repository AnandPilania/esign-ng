<?php


namespace Customer\Services;


use Core\Models\eCActivities;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Core\Helpers\DocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class eCLogService
{
    private $permissionService;

    /**
     * eCLogService constructor.
     * @param $permissionService
     */
    public function __construct(eCPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function initLogSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "VIEW_ACTION_HISTORY");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        return array('permission' => $permission);
    }

    public function searchLog($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "VIEW_ACTION_HISTORY", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $diff = date_diff(date_create($searchData["startDate"]), date_create($searchData["endDate"]));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) {
            throw new eCBusinessException('ACTION_HISTORY.ERROR_START_DATE_SMALLER_END_DATE');
        }
        if ($dateOver > 30) {
            throw new eCBusinessException('ACTION_HISTORY.ERROR_DATE_RANGE_OVER_30_DAYS');
        }
        $arr = array();
        $str = 'select * from ec_s_activities where company_id = ? ';
        $strCount = 'select count(*) as cnt from ec_s_activities where company_id = ? ';
        array_push($arr, $user->company_id);

        if (!empty($searchData["keyword"])) {
            $str .= ' AND ( name LIKE ? OR action LIKE ? OR note LIKE ? )';
            $strCount .= ' AND ( name LIKE ?  OR action LIKE ? OR note LIKE ? )';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if($user->is_personal == 1){
            $str .= ' AND email = ? ';
            $strCount .= ' AND email = ? ';
            array_push($arr, $user->email);
        }

        if($searchData["user"] != -1){
            $str .= ' AND name LIKE ? ';
            $strCount .= ' AND name LIKE ? ';
            array_push($arr, '%' . $searchData["user"] . '%');
        }

        if($searchData["action_group"]){
            $str .= ' AND action_group = ? ';
            $strCount .= ' AND action_group = ?';
            array_push($arr, $searchData["action_group"]);
        }

        if($searchData["action"] != -1){
            $str .= ' AND action LIKE ? ';
            $strCount .= ' AND action LIKE ?';
            array_push($arr, '%' . $searchData["action"] . '%');
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND created_at >= ? AND created_at <= ?';
            $strCount .= ' AND created_at >= ? AND created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND created_at >= ? ';
            $strCount .= ' AND created_at >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND created_at <= ?';
            $strCount .= ' AND created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        $action = eCActivities::select('action')
            ->where('company_id', $user->company_id)
            ->where('action_group', $searchData['action_group'])
            ->groupBy('action')
            ->orderBy('action', 'desc')
            ->get();

        $lstUsers = eCActivities::select('name')
            ->where('company_id', $user->company_id)
            ->groupBy('name')
            ->orderBy('name', 'desc')
            ->get();


        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, "lstAction" => $action, "lstUsers" => $lstUsers) ;
    }
}
