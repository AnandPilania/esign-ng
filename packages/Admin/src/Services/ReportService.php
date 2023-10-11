<?php

namespace Admin\Services;

use Admin\Services\Shared\NotificationService;
use Core\Helpers\Common;
use Core\Helpers\NotificationType;
use Core\Models\eCAgencies;
use Core\Models\eCCompany;
use Core\Models\eCDocuments;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Core\Models\eCAdmin;
use Customer\Services\eCConfigService;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Core\Services\ActionHistoryService;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportService
{

    private $notificationService;
    private $actionHistoryService;

    /**
     * eCUtilitiesService constructor.
     * @param NotificationService $notificationService
     * @param ActionHistoryService $actionHistoryService
     */
    public function __construct(NotificationService $notificationService, ActionHistoryService $actionHistoryService)
    {
        $this->notificationService = $notificationService;
        $this->actionHistoryService = $actionHistoryService;

    }


    public function init()
    {
        $user = Auth::user();

        $agencies = eCAgencies::all();
        $company = eCCompany::all();

        return array('lstAgency' => $agencies, 'lstCompany' => $company);
    }

    public function getTurnOver($searchData, $draw, $start, $limit)
    {
        $user = Auth::user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }

        $str = "SELECT COUNT(*) as total, MONTH(d.created_at) AS month, d.company_id, c.service_id, c.agency_id FROM ec_documents d join ec_companies c ON c.id = d.company_id where d.delete_flag = 0  AND d.parent_id = -1 AND d.document_state > 1 AND YEAR(d.created_at) = ? ";
        $arr = array($searchData['dashboardYear']);

        if($searchData['agency_id'] != -1) {
            $str .= " AND c.agency_id = ? ";
            array_push($arr,$searchData['agency_id']);
        }

        if ($searchData['company_id'] != -1){
            $str .= " AND d.company_id = ? ";
            array_push($arr,$searchData['company_id']);
        }

        $str .= " GROUP BY d.company_id,  MONTH(d.created_at)";

        $documents = DB::select($str, $arr);

        $turnOver = array(0,0,0,0,0,0,0,0,0,0,0,0);
        if ($documents) {
            foreach ($documents as $document) {
                $turnOver[$document->month - 1] += $document->total*1000;
            }
        }

//        $strDocument = "SELECT d.id, d.code, (SELECT COUNT(*) FROM ec_documents ds WHERE ds.parent_id = d.id AND ds.delete_flag = 0 AND ds.document_state > 3) AS addendum, d.sent_date, d.document_state, d.company_id, c.service_id, c.agency_id FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 3";
//        $strAgency = "SELECT a.id, FROM_BASE64(a.agency_name) AS name, a.status, a.created_at,( SELECT COUNT(*) FROM ec_companies c WHERE c.agency_id = a.id AND c.delete_flag = 0) AS company FROM ec_agencies a WHERE a.delete_flag = 0 ";
//        $strCompany = "SELECT c.id, c.name, c.agency_id, c.service_id, c.status, c.created_at,(SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 3";
//        $strDocumentCompany = "SELECT d.id, d.code, (SELECT COUNT(*) FROM ec_documents ds WHERE ds.parent_id = d.id AND ds.delete_flag = 0 AND ds.document_state > 3) AS addendum, d.sent_date, d.document_state, d.company_id, c.service_id, c.agency_id FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 3";
//        $where = "";
//        $arr = array();
//
//        if ($searchData["start_date"] && $searchData['end_date']) {
//            $strDocument .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
//            $strCompany .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
//            $strDocumentCompany .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
//            $where .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
//            array_push($arr, $searchData["startDate"]);
//            array_push($arr, $searchData["endDate"]);
//        } else if ($searchData["start_date"] && !$searchData['end_date']) {
//            $strDocument .= ' AND d.sent_date >= ? ';
//            $strCompany .= ' AND d.sent_date >= ? ';
//            $strDocumentCompany .= ' AND d.sent_date >= ? ';
//            $where .= ' AND d.sent_date >= ? ';
//            array_push($arr, $searchData["startDate"]);
//        } else if (!$searchData["start_date"] && $searchData['end_date']) {
//            $strDocument .= ' AND d.sent_date <= ? ';
//            $strCompany .= ' AND d.sent_date <= ? ';
//            $strDocumentCompany .= ' AND d.sent_date <= ? ';
//            $where .= ' AND d.sent_date <= ? ';
//            array_push($arr, $searchData["endDate"]);
//        }
//        $strCompany .= " ) AS total_doc FROM ec_companies c WHERE c.delete_flag = 0";
//        if($searchData["company_id"] != -1) {
//            $str = $strDocumentCompany;
//            $str .= " AND d.company_id = ? ";
//            array_push($arr, $searchData["company_id"]);
//            if ($searchData["keyword"]){
//                $str.= " AND d.name like ? ";
//                array_push($arr, '%' . $searchData["keyword"] . '%');
//            }
//            $strCount = "SELECT count(*) as cnt FROM ec_documents d WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 3" . $where . " AND d.company_id = ? ";
//
//        } else {
//            if($searchData["agency_id"] == -1) {
//                $str = $strAgency;
//                $arr = array();
//                if ($searchData["keyword"]){
//                    $str.= " AND FROM_BASE64(a.agency_name) like ? ";
//                    array_push($arr, '%' . $searchData["keyword"] . '%');
//                }
//                $strCount = "SELECT count(*) as cnt FROM ec_agencies WHERE delete_flag = 0";
//            } else {
//                $str = $strCompany . " AND agency_id = ?";
//                array_push($arr,$searchData["agency_id"]);
//                if ($searchData["keyword"]){
//                    $str.= " AND c.name like ? ";
//                    array_push($arr, '%' . $searchData["keyword"] . '%');
//                }
//                $strCount = "SELECT count(*) as cnt FROM ec_companies WHERE delete_flag = 0 AND agency_id = ?";
//            }
//        }
//
//
//        array_push($arr, $limit, $start);
//        $str .= " LIMIT ? OFFSET ?";
//
//        $resDoc = DB::select($strDocument, $arr);
//        $res = DB::select($str, $arr);
//        $resCount = DB::select($strCount,$arr);
//
//        foreach ($res as $r) {
//            $r->turn_over = 0;
//            foreach ($resDoc as $rDoc) {
//                if($searchData["agency_id"] == -1){
//                    if ($rDoc->agency_id == $r->id){
//                        $r->turn_over += 1000;
//                    }
//                } else {
//                    if ($rDoc->company_id == $r->id){
//                        $r->turn_over += 1000;
//                    }
//                }
//            }
//        }
        // fake data
        $res =[];
        $resCount[0]->cnt = 0;

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res,'initData' => $turnOver);
    }

    public function getCustomers($searchData, $draw, $start, $limit, $sortQuery) {
        $user = Auth::user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }
        // danh sách chi tiết báo cáo
        $str = "SELECT c.id, c.name, c.created_at, c.status, FROM_BASE64(a.agency_name) AS agency_name, (SELECT COUNT(*) FROM ec_documents d WHERE d.parent_id = -1 AND d.company_id = c.id AND d.delete_flag = 0 AND d.document_state > 3) AS total_doc, (SELECT MAX(d.created_at) FROM ec_documents d WHERE d.company_id = c.id) AS last_active FROM ec_companies c JOIN ec_agencies a ON a.id = c.agency_id WHERE c.delete_flag = 0 ";
        $strCount = "SELECT count(*) as cnt FROM ec_companies c WHERE c.delete_flag = 0 ";
        // select dashboard
        $strReport = "SELECT COUNT(*) AS total, MONTH(c.created_at) AS month, YEAR(c.created_at) AS year FROM ec_companies c WHERE c.delete_flag = 0 ";
        // khách hàng mới
        $strNew = "SELECT COUNT(*) As total FROM ec_companies c WHERE DATEDIFF(CURDATE(), c.created_at) < 30 AND c.delete_flag = 0 ";
        // khách hàng đang hoạt động và ngừng hoạt động
        $strActive = "SELECT COUNT(*) As total FROM ec_companies c WHERE c.delete_flag = 0 AND c.status = 1 ";
        $strPaused = "SELECT COUNT(*) As total FROM ec_companies c WHERE c.delete_flag = 0 AND c.status = 0 ";
        // khách hàng đã lâu không sử dụng dịch vụ
        $strNoUsed = "SELECT COUNT(*) As total FROM ec_companies c WHERE (SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND DATEDIFF(CURDATE(), d.created_at) < 30 AND d.document_state > 1 AND d.delete_flag = 0) = 0 AND DATEDIFF(CURDATE(), c.created_at) > 30 AND c.delete_flag = 0 ";

        $arr = array();
        if ($searchData['agency_id'] && $searchData['agency_id'] != -1) {
            $str .= " AND c.agency_id = ? ";
            $strCount .= " AND c.agency_id = ? ";
            $strReport .= " AND c.agency_id = ? ";
            $strNew .= " AND c.agency_id = ? ";
            $strActive .= " AND c.agency_id = ? ";
            $strPaused .= " AND c.agency_id = ? ";
            $strNoUsed .= " AND c.agency_id = ? ";
            array_push($arr, $searchData['agency_id']);
        }
        if ($searchData['new'] != -1) {
            $str .= " AND DATEDIFF(CURDATE(), c.created_at) < 30 ";
            $strCount .= " AND DATEDIFF(CURDATE(), c.created_at) < 30 ";
        }
        if ($searchData['status'] != -1){
            $str .= " AND c.status = ? ";
            $strCount .= " AND c.status = ? ";
            array_push($arr, $searchData['status']);
        }
        if ($searchData['no_used'] != -1) {
            $str .= " AND (SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND DATEDIFF(CURDATE(), d.created_at) < 30 AND d.document_state > 1 AND d.delete_flag = 0) = 0 AND DATEDIFF(CURDATE(), c.created_at) > 30 ";
            $strCount .= " AND (SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND DATEDIFF(CURDATE(), d.created_at) < 30 AND d.document_state > 1 AND d.delete_flag = 0) = 0 AND DATEDIFF(CURDATE(), c.created_at) > 30 ";
        }
        $strReport .= " GROUP BY MONTH(c.created_at), YEAR(c.created_at)";
        $str .= " ORDER BY c." . $sortQuery . ', c.id desc ';
        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        $resReport = DB::select($strReport, $arr);
        $resNew = DB::select($strNew, $arr);
        $resActive = DB::select($strActive, $arr);
        $resPaused = DB::select($strPaused, $arr);
        $resNoUsed = DB::select($strNoUsed, $arr);
        // chế tạo biểu đồ
        $company = array(0,0,0,0,0,0,0,0,0,0,0,0);
        $totalOldCompany = 0;

        foreach ($resReport as $r) {
            if ($r->year == $searchData['dashboardYear']) {
                $company[$r->month -1] += $r->total;
            } else if ($r->year < $searchData['dashboardYear']) {
                $totalOldCompany += $r->total;
            }
        }
        for ($i = 0; $i < 12; $i++) {
            if ($i == 0) {
                $company[$i] += $totalOldCompany;
            } else {
                $company[$i] += $company[$i - 1];
            }
        }
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, 'initData' => $company, "newCustomer" => $resNew[0]->total, "activeCustomer" => $resActive[0]->total, "pausedCustomer" => $resPaused[0]->total, "noUsed" => $resNoUsed[0]->total);
    }

    public function getDocuments($searchData, $draw, $start, $limit, $sortQuery) {
        $user = Auth::user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            throw new eCAuthenticationException();
        }
        // danh sách tài liệu
        $str = "SELECT d.id, d.code, d.sent_date, d.document_state, (SELECT COUNT(*) FROM ec_documents ds WHERE ds.parent_id = d.id AND ds.delete_flag = 0 AND ds.document_state > 1) AS addendum, (SELECT COUNT(*) FROM ec_document_assignees da WHERE da.document_id = d.id AND da.assign_type = 2) AS assignees FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1 ";
        $strCount = "SELECT count(*) as cnt FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1 ";
        // dashboard tài liệu
        $strReport = "SELECT COUNT(*) as total, MONTH(d.created_at) AS month FROM ec_documents d join ec_companies c ON c.id = d.company_id where d.delete_flag = 0 AND d.parent_id = -1 AND d.document_state > 1 ";
        $strReportData = "SELECT COUNT(*) as total, d.document_state FROM ec_documents d join ec_companies c ON c.id = d.company_id where d.delete_flag = 0 AND d.parent_id = -1 AND d.document_state > 1 ";
        $strPieData = "SELECT COUNT(*) as total, d.document_state FROM ec_documents d join ec_companies c ON c.id = d.company_id where d.delete_flag = 0 AND d.parent_id = -1 AND d.document_state > 1 ";

        $arr = array();

        if (isset($searchData['dashboardYear'])) {
            $str .= " AND YEAR(d.created_at) = ?  ";
            $strCount .= " AND YEAR(d.created_at) = ?  ";
            $strReport .= " AND YEAR(d.created_at) = ?  ";
            $strReportData .= " AND YEAR(d.created_at) = ?  ";
            $strPieData .= " AND YEAR(d.created_at) = ?  ";
            array_push($arr,$searchData['dashboardYear']);
        }

        if (isset($searchData['agency_id']) && $searchData['agency_id'] != -1) {
            $str .= " AND c.agency_id = ? ";
            $strCount .= " AND c.agency_id = ? ";
            $strReport .= " AND c.agency_id = ? ";
            $strReportData .= " AND c.agency_id = ? ";
            $strPieData .= " AND c.agency_id = ? ";
            array_push($arr, $searchData['agency_id']);
        }
        if (isset($searchData['company_id']) && $searchData['company_id'] != -1) {
            $str .= " AND d.company_id = ? ";
            $strCount .= " AND d.company_id = ? ";
            $strReport .= " AND d.company_id = ? ";
            $strReportData .= " AND d.company_id = ? ";
            $strPieData .= " AND d.company_id = ? ";
            array_push($arr, $searchData['company_id']);
        }
        if (isset($searchData["start_date"]) && isset($searchData['end_date'])) {
            $str .= ' AND d.created_at >= ? AND d.created_at <= ?';
            $strCount .= ' AND d.created_at >= ? AND d.created_at <= ?';
            $strReport .= ' AND d.created_at >= ? AND d.created_at <= ?';
            $strReportData .= ' AND d.created_at >= ? AND d.created_at <= ?';
            $strPieData .= ' AND d.created_at >= ? AND d.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if (isset($searchData["start_date"]) && !isset($searchData['end_date'])) {
            $str .= ' AND d.created_at >= ? ';
            $strCount .= ' AND d.created_at >= ? ';
            $strReport .= ' AND d.created_at >= ? ';
            $strReportData .= ' AND d.created_at >= ? ';
            $strPieData .= ' AND d.created_at >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!isset($searchData["start_date"]) && isset($searchData['end_date'])) {
            $str .= ' AND d.created_at <= ?';
            $strCount .= ' AND d.created_at <= ? ';
            $strReport .= ' AND d.created_at <= ? ';
            $strReportData .= ' AND d.created_at <= ? ';
            $strPieData .= ' AND d.created_at <= ? ';
            array_push($arr, $searchData["endDate"]);
        }
        if(isset($searchData['completed']) && $searchData['completed']) {
            $str .= " AND d.document_state IN (8,11) ";
            $strReport .= " AND d.document_state IN (8,11) ";
            $strPieData .= " AND d.document_state IN (8,11) ";
            $strCount .= " AND d.document_state IN (8,11) ";
        }
        if(isset($searchData['in_process']) && $searchData['in_process']) {
            $str .= " AND d.document_state NOT IN (6,8,11,4,5) ";
            $strReport .= " AND d.document_state NOT IN (6,8,11,4,5) ";
            $strPieData .= " AND d.document_state NOT IN (6,8,11,4,5) ";
            $strCount .= " AND d.document_state NOT IN (6,8,11,4,5) ";
        }
        if(isset($searchData['abort']) && $searchData['abort']) {
            $str .= " AND d.document_state = 6 ";
            $strReport .= " AND d.document_state = 6 ";
            $strPieData .= " AND d.document_state = 6 ";
            $strCount .= " AND d.document_state = 6 ";
        }
        if(isset($searchData['cancel']) && $searchData['cancel']) {
            $str .= " AND d.document_state = 4 ";
            $strReport .= " AND d.document_state = 4 ";
            $strPieData .= " AND d.document_state = 4 ";
            $strCount .= " AND d.document_state = 4 ";
        }
        if(isset($searchData['overdue']) && $searchData['overdue']) {
            $str .= " AND d.document_state = 5 ";
            $strReport .= " AND d.document_state = 5 ";
            $strPieData .= " AND d.document_state = 5 ";
            $strCount .= " AND d.document_state = 5 ";
        }
        if(isset($searchData['verify_fail']) && $searchData['verify_fail']) {
            $str .= " AND d.document_state = 10 ";
            $strReport .= " AND d.document_state = 10 ";
            $strPieData .= " AND d.document_state = 10 ";
            $strCount .= " AND d.document_state = 10 ";
        }
        if(isset($searchData['not_authorize']) && $searchData['not_authorize']) {
            $str .= " AND d.document_state = 7 ";
            $strReport .= " AND d.document_state = 7 ";
            $strPieData .= " AND d.document_state = 7 ";
            $strCount .= " AND d.document_state = 7 ";
        }

        $strReport .= " GROUP BY MONTH(d.created_at) ";
        $strReportData .= " GROUP BY d.document_state ";
        $strPieData .= " GROUP BY d.document_state ";
        $str .= " ORDER BY d." . $sortQuery . ', d.id desc ';
        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        $resReport = DB::select($strReport, $arr);
        $resPieData = DB::select($strPieData, $arr);
        $resReportData = DB::select($strReportData, $arr);
        // chế tạo biểu đồ
        $document = array(0,0,0,0,0,0,0,0,0,0,0,0);

        foreach ($resReport as $r) {
            // fake tạm đơn giá
            // $r->price = 1000;
            $document[$r->month -1] += $r->total;
        }
        foreach ($res as $r) {
            if(($r->document_state == 8 || $r->document_state == 11) && $r->addendum > 0 ){
                $priceAddendum = eCDocuments::select(DB::raw('sum(price) as price'))->where('parent_id', $r->id)->where('document_state',8)->get();
                $r->price += $priceAddendum[0]->price;
            }
        }

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, 'initData' => $document, "report" => $resReportData, "pie" => $resPieData);
    }

    public function exportCustomer ($request) {

        $str = "SELECT d.id, d.code, d.sent_date, d.document_state, (SELECT COUNT(*) FROM ec_documents ds WHERE ds.parent_id = d.id AND ds.delete_flag = 0 AND ds.document_state > 1) AS addendum, (SELECT COUNT(*) FROM ec_document_assignees da WHERE da.document_id = d.id AND da.assign_type = 2) AS assignees FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1 AND YEAR(d.created_at) = ? ";
        $arr = array();
        if ($request->agency_id != -1) {
            $str .= " AND c.agency_id = ? ";
            array_push($arr, $request->agency_id);
        }
        if ($request->new != -1) {
            $str .= " AND DATEDIFF(CURDATE(), c.created_at) < 30 ";
        }
        if ($request->status != -1){
            $str .= " AND c.status = ? ";
            array_push($arr, $searchData['status']);
        }
        if ($request->no_used != -1) {
            $str .= " AND (SELECT COUNT(*) FROM ec_documents d WHERE d.company_id = c.id AND DATEDIFF(CURDATE(), d.created_at) < 30 AND d.document_state > 1 AND d.delete_flag = 0) = 0 AND DATEDIFF(CURDATE(), c.created_at) > 30 ";
        }
        $records = DB::select($str, $arr);

        // Tạo header
        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Chi tiết dánh sách khách hàng');
        $activeSheet->setCellValue('A1', 'Tên khách hàng');
        $activeSheet->setCellValue('B1', 'Đại lý');
        $activeSheet->setCellValue('C1', 'Tổng số tài liệu');
        $activeSheet->setCellValue('D1', 'Hoạt động lần cuối');
        $activeSheet->setCellValue('E1', 'Trạng thái');
        $activeSheet->setCellValue('F1', 'Ngày tạo');


        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'F') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }

        $i = 2;
        foreach ($records as $record) {
            $status = "";
            if ($record->status == 0) {
                $status = 'Ngưng hoạt động';
            } else if ($record->status == 1) {
                $status = 'Hoạt động';
            } else{
                return;
            }
            $activeSheet->setCellValue('A' . $i, $record->name);
            $activeSheet->setCellValue('B' . $i, $record->agency_name);
            $activeSheet->setCellValue('C' . $i, $record->total_doc);
            $activeSheet->setCellValue('D' . $i, $record->last_active);
            $activeSheet->setCellValue('E' . $i, $status);
            $activeSheet->setCellValue('F' . $i, $record->created_at);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }

    public function exportDocuments ($request) {
        $str = "SELECT d.id, d.code, d.sent_date, d.document_state, d.customer_id, d.updated_at, (SELECT COUNT(*) FROM ec_documents ds WHERE ds.parent_id = d.id AND ds.delete_flag = 0 AND ds.document_state > 1) AS addendum, (SELECT COUNT(*) FROM ec_document_assignees da WHERE da.document_id = d.id AND da.assign_type = 2) AS assignees FROM ec_documents d JOIN ec_companies c ON c.id = d.company_id WHERE d.parent_id = -1 AND d.delete_flag = 0 AND d.document_state > 1 ";
        $arr = array();
        if (isset($request->dashboardYear)){
            $str .= " AND YEAR(d.created_at) = ? ";
            array_push($arr, $request->dashboardYear);
        }
        if (isset($request->agency_id) && $request->agency_id != -1) {
            $str .= " AND c.agency_id = ? ";
            array_push($arr, $request->agency_id);
        }
        if (isset($request->company_id) && $request->company_id != -1) {
            $str .= " AND d.company_id = ? ";
            array_push($arr, $request->company_id);
        }
        if(isset($request->completed) && $request->completed != -1) {
            $str .= " AND d.document_state IN (8,11) ";
        }
        if(isset($request->in_process) && $request->in_process != -1) {
            $str .= " AND d.document_state NOT IN (6,8,11) ";
        }
        if(isset($request->abort) && $request->abort != -1) {
            $str .= " AND d.document_state = 6 ";
        }
        if(isset($request->cancel) && $request->cancel != -1) {
            $str .= " AND d.document_state = 4 ";
        }
        if(isset($request->overdue) && $request->overdue != -1) {
            $str .= " AND d.document_state = 5 ";
        }
        if(isset($request->not_authorize) && $request->not_authorize != -1) {
            $str .= " AND d.document_state = 7 ";
        }
        if(isset($request->verify_fail) && $request->verify_fail != -1) {
            $str .= " AND d.document_state = 10 ";
        }
        if (isset($request->start_date) && isset($request->end_date)) {
            $str .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
            array_push($arr, $request->startDate);
            array_push($arr, $request->endDate);
        } else if (isset($request->start_date) && !isset($request->end_date)) {
            $str .= ' AND d.sent_date >= ? ';
            array_push($arr, $request->startDate);
        } else if (!isset($request->start_date) && isset($request->end_date)) {
            $str .= ' AND d.sent_date <= ?';
            array_push($arr, $request->endDate);
        }

        $records = DB::select($str, $arr);
        // Tạo header
        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Chi tiết sử dụng dịch vụ');
        $activeSheet->setCellValue('A1', 'Mã tài liệu');
        $activeSheet->setCellValue('B1', 'Số lượng phụ lục');
        $activeSheet->setCellValue('C1', 'Ngày tài liệu');
        $activeSheet->setCellValue('D1', 'Tổng thu');
        $activeSheet->setCellValue('E1', 'Trạng thái');
        $activeSheet->setCellValue('F1', 'Số người ký');


        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'F') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }

        $i = 2;
        foreach ($records as $record) {
            $status = "";
            $price = 0;
            switch ($record->document_state){
                case 1:
                    $status = "Lưu nháp";
                    break;
                case 2:
                    $status = "Chờ duyệt";
                    break;
                case 3:
                    $status = "Chờ ký số";
                    break;
                case 4:
                    $status = "Bị từ chối";
                    break;
                case 5:
                    $status = "Quá hạn";
                    break;
                case 6:
                    $status = "Hủy bỏ";
                    break;
                case 7:
                    $status = "Chưa xác thực";
                    break;
                case 8:
                    $status = "Đã hoàn thành";
                    break;
                case 9:
                    $status = "Đang hoàn thành";
                    break;
                case 10:
                    $status = "Xác thực không thành công";
                    break;
                case 11:
                    $status = "Đã hết hiệu lực";
                    break;
            }
            $activeSheet->setCellValue('A' . $i, $record->code);
            $activeSheet->setCellValue('B' . $i, $record->addendum);
            $activeSheet->setCellValue('C' . $i, $record->sent_date);
            $activeSheet->setCellValue('D' . $i, $price);
            $activeSheet->setCellValue('E' . $i, $status);
            $activeSheet->setCellValue('F' . $i, $record->assignees);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }
}
