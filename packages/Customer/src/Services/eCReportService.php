<?php


namespace Customer\Services;


use Core\Models\eCDocumentGroups;
use Core\Models\eCDocumentTypes;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;
use Customer\Services\Shared\eCPermissionService;
use Core\Helpers\DocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class eCReportService
{
    private $permissionService;

    /**
     * eCReportService constructor.
     * @param $permissionService
     */
    public function __construct(eCPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function initInternalDocumentSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_INTERNAL");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', DocumentType::INTERNAL)
            ->get();
        return array('permission' => $permission, 'lstDocumentType' => $lstDocumentType);
    }

    public function searchInternalDocument($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "REPORT_INTERNAL", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $diff = date_diff(date_create($searchData["startDate"]), date_create($searchData["endDate"]));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) {
            throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        }
        if ($dateOver > 30) {
            throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');
        }

        // data to render table

        $str = "SELECT ed.*, dt.dc_type_name, dt.dc_style FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = " . DocumentType::INTERNAL;
        $strCount = 'SELECT count(*) as cnt FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = ' . DocumentType::INTERNAL;

        // data to render chart
        $report = "SELECT ed.document_type_id, ed.document_state, ed.created_at, ed.finished_date, dt.dc_style FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = " . DocumentType::INTERNAL;
        $arr = array($user->company_id);

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND ed.document_type_id = ?';
            $strCount .= ' AND document_type_id = ?';
            $report .= ' AND ed.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["dc_style"] != -1) {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            $report .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }

        if ($searchData["document_state"] != -1 && in_array($searchData["document_state"], array("4", "6", "8"))) {
            $str .= ' AND ed.document_state = ?';
            $strCount .= ' AND document_state = ?';
            $report .= ' AND ed.document_state = ?';
            array_push($arr, $searchData["document_state"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $report .= ' AND ed.created_at >= ? AND ed.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? ';
            $strCount .= ' AND ed.sent_date >= ? ';
            $report .= ' AND ed.created_at >= ?';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date <= ?';
            $report .= ' AND ed.created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $res_report = DB::select($report, $arr);

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);
        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, 'data_report' => $res_report);
    }

    public function exportInternalDocument($startDate, $endDate, $document_state, $document_type_id, $dc_style)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_INTERNAL");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }

        $diff = date_diff(date_create($startDate), date_create($endDate));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) {
            throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        }
        if ($dateOver > 30) {
            throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');
        }

        $str = "SELECT ed.*, dt.dc_type_name, dt.dc_style FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = " . DocumentType::INTERNAL;
        $arr = array($user->company_id);

        if ($document_state != -1 && in_array($document_state, array("4", "6", "8"))) {
            $str .= ' AND ed.document_state = ?';
            array_push($arr, $document_state);
        }

        if ($document_type_id != -1) {
            $str .= ' AND ed.document_type_id = ?';
            array_push($arr, $document_type_id);
        }

        if ($dc_style != -1) {
            $str .= ' AND dt.dc_style = ?';
            array_push($arr, $dc_style);
        }

        if ($startDate && $endDate) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            array_push($arr, $startDate);
            array_push($arr, $endDate);
        } else if ($startDate && !$endDate) {
            $str .= ' AND ed.sent_date >= ? ';
            array_push($arr, $startDate);
        } else if (!$startDate && $endDate) {
            $str .= ' AND ed.sent_date <= ?';
            array_push($arr, $endDate);
        }

        $records = DB::select($str, $arr);

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Báo cáo tài liệu nội bộ');

        // Tạo header
        $activeSheet->setCellValue('A1', 'Mã số tài liệu');
        $activeSheet->setCellValue('B1', 'Tên tài liệu');
        $activeSheet->setCellValue('C1', 'Loại tài liệu');
        $activeSheet->setCellValue('D1', 'Dạng tài liệu');
        $activeSheet->setCellValue('E1', 'Ngày tạo');
        $activeSheet->setCellValue('F1', 'Hạn giao kết');
        $activeSheet->setCellValue('G1', 'Ngày hoàn thành');
        $activeSheet->setCellValue('H1', 'Trạng thái');

        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'H') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }
        // Điền dữ liệu
        $i = 2;
        foreach ($records as $record) {
            $document_state = "";
            // $assign_type = "";
            // $assignee_state = "";
            if ($record->document_state == 1) {
                $document_state = 'Lưu nháp';
            } else if ($record->document_state == 2) {
                $document_state = 'Chờ duyệt';
            } else if ($record->document_state == 3) {
                $document_state = 'Chờ ký số';
            } else if ($record->document_state == 4) {
                $document_state = 'Bị từ chối';
            } else if ($record->document_state == 5) {
                $document_state = 'Quá hạn';
            } else if ($record->document_state == 6) {
                $document_state = 'Hủy bỏ';
            } else if ($record->document_state == 7) {
                $document_state = 'Chưa xác thực';
            } else if ($record->document_state == 8) {
                $document_state = 'Đã hoàn thành';
            } else if ($record->document_state == 9) {
                $document_state = 'Đang xác thực';
            } else if ($record->document_state == 10) {
                $document_state = 'Xác thực không thành công';
            }
            $dc_style = "";
            if ($record->dc_style == 0){
                $dc_style = 'Khác';
            } else if ($record->dc_style == 1) {
                $dc_style = 'Mua vào';
            } else if ($record->dc_style == 2) {
                $dc_style = 'Bán ra';
            }

            // if ($record->assign_type == 0) {
            //     $assign_type = 'Người tạo';
            // } else if ($record->assign_type == 1) {
            //     $assign_type = 'Người phê duyệt';
            // } else if ($record->assign_type == 2) {
            //     $assign_type = 'Người ký';
            // } else if ($record->assign_type == 3) {
            //     $assign_type = 'Người xem';
            // }

            // if ($record->assignee_state == 0) {
            //     $assignee_state = 'Chưa nhận thông báo';
            // } else if ($record->assignee_state == 1) {
            //     $assignee_state = 'Đã nhận thông báo';
            // } else if ($record->assignee_state == 2) {
            //     $assignee_state = 'Đã giao kết';
            // } else if ($record->assignee_state == 3) {
            //     $assignee_state = 'Từ chối';
            // }

            // $activeSheet->setCellValue('A' . $i, $record->sent_date);
            // $activeSheet->setCellValue('B' . $i, $record->code);
            // $activeSheet->setCellValue('C' . $i, $record->name);
            // $activeSheet->setCellValue('D' . $i, $record->dc_type_name);
            // $activeSheet->setCellValue('E' . $i, $record->expired_date);
            // $activeSheet->setCellValue('F' . $i, $record->finished_date);
            // $activeSheet->setCellValue('G' . $i, $record->created_at);
            // $activeSheet->setCellValue('H' . $i, $document_state);
            // $activeSheet->setCellValue('I' . $i, $record->assignee_name);
            // $activeSheet->setCellValue('J' . $i, $record->assignee_email);
            // $activeSheet->setCellValue('K' . $i, $record->assignee_phone);
            // $activeSheet->setCellValue('L' . $i, $assign_type);
            // $activeSheet->setCellValue('M' . $i, $assignee_state);
            $activeSheet->setCellValue('A' . $i, $record->code);
            $activeSheet->setCellValue('B' . $i, $record->name);
            $activeSheet->setCellValue('C' . $i, $record->dc_type_name);
            $activeSheet->setCellValue('D' . $i, $dc_style);
            $activeSheet->setCellValue('E' . $i, $record->created_at);
            $activeSheet->setCellValue('F' . $i, $record->expired_date);
            $activeSheet->setCellValue('G' . $i, $record->finished_date);
            $activeSheet->setCellValue('H' . $i, $document_state);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }

    public function initCommerceDocumentSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_COMMERCE");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->where('document_group_id', DocumentType::COMMERCE)
            ->get();
        return array('permission' => $permission, 'lstDocumentType' => $lstDocumentType);
    }

    public function searchCommerceDocument($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "REPORT_COMMERCE", true, false, false, false);
        if (!$hasPermission) {
            throw new eCAuthenticationException();
        }

        $diff = date_diff(date_create($searchData["startDate"]), date_create($searchData["endDate"]));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) {
            throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        }
        if ($dateOver > 30) {
            throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');
        }

        // data to render table

        $str = "SELECT ed.*, dt.dc_type_name, dt.dc_style FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = " . DocumentType::COMMERCE;
        $strCount = 'SELECT count(*) as cnt FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = ' . DocumentType::COMMERCE;

        // data to render chart
        $report = "SELECT ed.document_type_id, ed.document_state, ed.created_at, ed.finished_date, dt.dc_style FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = " . DocumentType::COMMERCE;
        $arr = array($user->company_id);

        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND ed.document_type_id = ?';
            $strCount .= ' AND document_type_id = ?';
            $report .= ' AND document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["document_state"] != -1 && in_array($searchData["document_state"], array("4", "6", "8"))) {
            $str .= ' AND ed.document_state = ?';
            $strCount .= ' AND document_state = ?';
            $report .= ' AND ed.document_state = ?';
            array_push($arr, $searchData["document_state"]);
        }

        if ($searchData["dc_style"] != -1) {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            $report .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            $report .= ' AND ed.created_at >= ? AND ed.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND ed.sent_date >= ? ';
            $strCount .= ' AND ed.sent_date >= ? ';
            $report .= ' AND ed.created_at >= ?';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND ed.sent_date <= ?';
            $strCount .= ' AND ed.sent_date <= ?';
            $report .= ' AND ed.created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $res_report = DB::select($report, $arr);

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, 'data_report' => $res_report);
    }

    public function exportCommerceDocument($startDate, $endDate, $document_state, $document_type_id, $dc_style)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_COMMERCE");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }

        $diff = date_diff(date_create($startDate), date_create($endDate));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) {
            throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        }
        if ($dateOver > 30) {
            throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');
        }

        $str = "SELECT ed.*, dt.dc_type_name, dt.dc_style FROM ec_documents AS ed JOIN s_document_types AS dt ON dt.id = ed.document_type_id WHERE ed.company_id = ? AND ed.delete_flag = 0 AND ed.parent_id = -1 AND ed.document_type = " . DocumentType::COMMERCE;
        $arr = array($user->company_id);

        if ($document_state != -1 && in_array($document_state, array("4", "6", "8"))) {
            $str .= ' AND ed.document_state = ?';
            array_push($arr, $document_state);
        }

        if ($document_type_id != -1) {
            $str .= ' AND ed.document_type_id = ?';
            array_push($arr, $document_type_id);
        }

        if ($dc_style != -1) {
            $str .= ' AND dt.dc_style = ?';
            array_push($arr, $dc_style);
        }

        if ($startDate && $endDate) {
            $str .= ' AND ed.sent_date >= ? AND ed.sent_date <= ?';
            array_push($arr, $startDate);
            array_push($arr, $endDate);
        } else if ($startDate && !$endDate) {
            $str .= ' AND ed.sent_date >= ? ';
            array_push($arr, $startDate);
        } else if (!$startDate && $endDate) {
            $str .= ' AND ed.sent_date <= ?';
            array_push($arr, $endDate);
        }

        $records = DB::select($str, $arr);

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Báo cáo tài liệu thương mại');

        // Tạo header
        $activeSheet->setCellValue('A1', 'Mã số tài liệu');
        $activeSheet->setCellValue('B1', 'Tên tài liệu');
        $activeSheet->setCellValue('C1', 'Loại tài liệu');
        $activeSheet->setCellValue('D1', 'Dạng tài liệu');
        $activeSheet->setCellValue('E1', 'Ngày tạo');
        $activeSheet->setCellValue('F1', 'Hạn giao kết');
        $activeSheet->setCellValue('G1', 'Ngày hoàn thành');
        $activeSheet->setCellValue('H1', 'Trạng thái');

        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'H') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }
        // Điền dữ liệu
        $i = 2;
        foreach ($records as $record) {
            $document_state = "";
            // $assign_type = "";
            // $assignee_state = "";
            if ($record->document_state == 1) {
                $document_state = 'Lưu nháp';
            } else if ($record->document_state == 2) {
                $document_state = 'Chờ duyệt';
            } else if ($record->document_state == 3) {
                $document_state = 'Chờ ký số';
            } else if ($record->document_state == 4) {
                $document_state = 'Bị từ chối';
            } else if ($record->document_state == 5) {
                $document_state = 'Quá hạn';
            } else if ($record->document_state == 6) {
                $document_state = 'Hủy bỏ';
            } else if ($record->document_state == 7) {
                $document_state = 'Chưa xác thực';
            } else if ($record->document_state == 8) {
                $document_state = 'Đã hoàn thành';
            } else if ($record->document_state == 9) {
                $document_state = 'Đang xác thực';
            } else if ($record->document_state == 10) {
                $document_state = 'Xác thực không thành công';
            }
            $dc_style = "";
            if ($record->dc_style == 0){
                $dc_style = 'Khác';
            } else if ($record->dc_style == 1) {
                $dc_style = 'Mua vào';
            } else if ($record->dc_style == 2) {
                $dc_style = 'Bán ra';
            }

            // if ($record->assign_type == 0) {
            //     $assign_type = 'Người tạo';
            // } else if ($record->assign_type == 1) {
            //     $assign_type = 'Người phê duyệt';
            // } else if ($record->assign_type == 2) {
            //     $assign_type = 'Người ký';
            // } else if ($record->assign_type == 3) {
            //     $assign_type = 'Người xem';
            // }

            // if ($record->assignee_state == 0) {
            //     $assignee_state = 'Chưa nhận thông báo';
            // } else if ($record->assignee_state == 1) {
            //     $assignee_state = 'Đã nhận thông báo';
            // } else if ($record->assignee_state == 2) {
            //     $assignee_state = 'Đã giao kết';
            // } else if ($record->assignee_state == 3) {
            //     $assignee_state = 'Từ chối';
            // }

            // $activeSheet->setCellValue('A' . $i, $record->sent_date);
            // $activeSheet->setCellValue('B' . $i, $record->code);
            // $activeSheet->setCellValue('C' . $i, $record->name);
            // $activeSheet->setCellValue('D' . $i, $record->dc_type_name);
            // $activeSheet->setCellValue('E' . $i, $record->expired_date);
            // $activeSheet->setCellValue('F' . $i, $record->finished_date);
            // $activeSheet->setCellValue('G' . $i, $record->created_at);
            // $activeSheet->setCellValue('H' . $i, $document_state);
            // $activeSheet->setCellValue('I' . $i, $record->assignee_name);
            // $activeSheet->setCellValue('J' . $i, $record->assignee_email);
            // $activeSheet->setCellValue('K' . $i, $record->assignee_phone);
            // $activeSheet->setCellValue('L' . $i, $assign_type);
            // $activeSheet->setCellValue('M' . $i, $assignee_state);
            $activeSheet->setCellValue('A' . $i, $record->code);
            $activeSheet->setCellValue('B' . $i, $record->name);
            $activeSheet->setCellValue('C' . $i, $record->dc_type_name);
            $activeSheet->setCellValue('D' . $i, $dc_style);
            $activeSheet->setCellValue('E' . $i, $record->created_at);
            $activeSheet->setCellValue('F' . $i, $record->expired_date);
            $activeSheet->setCellValue('G' . $i, $record->finished_date);
            $activeSheet->setCellValue('H' . $i, $document_state);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }

    public function initSendMessageSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_SEND_MESSAGE");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $lstDocumentGroup = eCDocumentGroups::where('status', 1)->get();

        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)
            ->where('status', 1)
            ->get();
        return array('permission' => $permission, 'lstDocumentType' => $lstDocumentType, 'lstDocumentGroup' => $lstDocumentGroup);
    }

    public function searchSendMessage($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "REPORT_SEND_MESSAGE", true, false, false, false);
        if (!$hasPermission)  throw new eCAuthenticationException();

        $diff = date_diff(date_create($searchData["startDate"]), date_create($searchData["endDate"]));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        if ($dateOver > 30) throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');

        $str = 'SELECT c.id, c.created_at, FROM_BASE64(u.name) AS sender_name, a.full_name AS receiver_name, a.phone AS receiver_phone, a.email as receiver_email, d.sent_date, d.code, d.name, c.send_type, c.`status` FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types AS dt ON dt.id = d.document_type_id WHERE d.company_id = ? AND c.delete_flag = 0 ';
        $strCount = 'SELECT count(*) as cnt FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types AS dt ON dt.id = d.document_type_id WHERE d.company_id = ? AND c.delete_flag = 0 ';
        $arr = array($user->company_id);

        $report = 'SELECT count(*) as cnt FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN s_document_types AS dt ON dt.id = d.document_type_id WHERE d.company_id = ? AND c.delete_flag = 0 ';
        if ($searchData["document_type_id"] != -1) {
            $str .= ' AND d.document_type_id = ?';
            $report .= ' AND d.document_type_id = ?';
            $strCount .= ' AND d.document_type_id = ?';
            array_push($arr, $searchData["document_type_id"]);
        }

        if ($searchData["document_group_id"] != -1) {
            $str .= ' AND d.document_type = ?';
            $report .= ' AND d.document_type = ?';
            $strCount .= ' AND d.document_type = ?';
            array_push($arr, $searchData["document_group_id"]);
        }

        if ($searchData["dc_style"] != -1) {
            $str .= ' AND dt.dc_style = ?';
            $strCount .= ' AND dt.dc_style = ?';
            $report .= ' AND dt.dc_style = ?';
            array_push($arr, $searchData["dc_style"]);
        }

        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND c.created_at >= ? AND c.created_at <= ?';
            $report .= ' AND c.created_at >= ? AND c.created_at <= ?';
            $strCount .= ' AND c.created_at >= ? AND c.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND c.created_at >= ? ';
            $report .= ' AND c.created_at >= ? ';
            $strCount .= ' AND c.created_at >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND c.created_at <= ?';
            $report .= ' AND c.created_at >= ? ';
            $strCount .= ' AND c.created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $sms_success = $report . ' AND c.send_type = 0 and c.status = 1';
        $sms_failed = $report . ' AND c.send_type = 0 and c.status = 2';
        $sms_not_send = $report . ' AND c.send_type = 0 and c.status = 0';
        $email_success = $report . ' AND c.send_type = 1 and c.status = 1';
        $email_failed = $report . ' AND c.send_type = 1 and c.status = 2';
        $email_not_send = $report . ' AND c.send_type = 1 and c.status = 0';

        $total_sms_success = DB::select($sms_success, $arr);
        $total_sms_failed = DB::select($sms_failed, $arr);
        $total_sms_not_send = DB::select($sms_not_send, $arr);
        $total_email_success = DB::select($email_success, $arr);
        $total_email_failed = DB::select($email_failed, $arr);
        $total_email_not_send = DB::select($email_not_send, $arr);

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, "total_sms_success" => $total_sms_success[0]->cnt, "total_sms_failed" => $total_sms_failed[0]->cnt, "total_sms_not_send" => $total_sms_not_send[0]->cnt, "total_email_success" => $total_email_success[0]->cnt, "total_email_failed" => $total_email_failed[0]->cnt, "total_email_not_send" => $total_email_not_send[0]->cnt);
    }

    public function exportSendMessage($startDate, $endDate, $keyword, $document_group_id, $document_type_id, $dc_style)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_SEND_MESSAGE");
        if (!$permission || $permission->is_view != 1)  throw new eCAuthenticationException();

        $diff = date_diff(date_create($startDate), date_create($endDate));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        if ($dateOver > 30)throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');

        $str = 'SELECT c.id, c.created_at, FROM_BASE64(u.name) AS sender_name, a.full_name AS receiver_name, a.phone AS receiver_phone, a.email as receiver_email, d.sent_date, d.code, d.name, c.`status` FROM ec_document_conversations c JOIN ec_documents d ON c.document_id = d.id JOIN ec_document_assignees a ON c.send_id = a.id JOIN ec_users u ON d.created_by = u.id JOIN s_document_types AS dt ON dt.id = d.document_type_id WHERE d.company_id = ? AND c.delete_flag = 0 ';
        $arr = array($user->company_id);
        if ($document_type_id != -1) {
            $str .= ' AND d.document_type_id = ?';
            array_push($arr, $document_type_id);
        }

        if ($document_group_id != -1) {
            $str .= ' AND d.document_type_id = ?';
            array_push($arr, $document_group_id);
        }

        if ($dc_style != -1) {
            $str .= ' AND dt.dc_style = ?';
            array_push($arr, $dc_style);
        }

        if ($startDate && $endDate) {
            $str .= ' AND c.created_at >= ? AND c.created_at <= ?';
            array_push($arr, $startDate);
            array_push($arr, $endDate);
        } else if ($startDate && !$endDate) {
            $str .= ' AND c.created_at >= ? ';
            array_push($arr, $startDate);
        } else if (!$startDate && $endDate) {
            $str .= ' AND c.created_at <= ? ';
            array_push($arr, $endDate);
        }

        $records = DB::select($str, $arr);

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Báo cáo gửi tin');

        // Tạo header
        $activeSheet->setCellValue('A1', 'Ngày gửi');
        $activeSheet->setCellValue('B1', 'Người gửi');
        $activeSheet->setCellValue('C1', 'Người nhận');
        $activeSheet->setCellValue('D1', 'Số điện thoại');
        $activeSheet->setCellValue('E1', 'Email người nhận');
        $activeSheet->setCellValue('F1', 'Mã số tài liệu');
        $activeSheet->setCellValue('G1', 'Tên tài liệu');
        $activeSheet->setCellValue('H1', 'Trạng thái');

        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'H') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }
        // Điền dữ liệu
        $i = 2;
        foreach ($records as $record) {
            $status = "";

            if ($record->status == 0) {
                $status = 'Chưa nhận thông báo';
            } else if ($record->status == 1) {
                $status = 'Đã nhận thông báo';
            } else if ($record->status == 2) {
                $status = 'Đã giao kết';
            } else if ($record->status == 3) {
                $status = 'Từ chối';
            }

            $activeSheet->setCellValue('A' . $i, $record->created_at);
            $activeSheet->setCellValue('B' . $i, $record->sender_name);
            $activeSheet->setCellValue('C' . $i, $record->receiver_name);
            $activeSheet->setCellValue('D' . $i, $record->receiver_phone);
            $activeSheet->setCellValue('E' . $i, $record->receiver_email);
            $activeSheet->setCellValue('F' . $i, $record->code);
            $activeSheet->setCellValue('G' . $i, $record->name);
            $activeSheet->setCellValue('H' . $i, $status);
            $i++;
        }
        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }

    public function initSignEkycSetting()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_SIGN_EKYC");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }

        $lstDocumentGroup = eCDocumentGroups::where('status', 1)->get();

        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)->where('status', 1)->get();
        return array('permission' => $permission, 'lstDocumentType' => $lstDocumentType, 'lstDocumentGroup' => $lstDocumentGroup);
    }

    public function searchSignEkyc($searchData, $draw, $start, $limit, $sortQuery)
    {
        $user = Auth::user();
        $hasPermission = $this->permissionService->checkPermission($user->role_id, "REPORT_SIGN_EKYC", true, false, false, false);
        if (!$hasPermission) throw new eCAuthenticationException();

        $diff = date_diff(date_create($searchData["startDate"]), date_create($searchData["endDate"]));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        if ($dateOver > 30) throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');

        $str = 'SELECT kl.id, kl.code, kl.type, kl.created_at, kl.start_time, kl.end_time FROM ec_kyc_log kl  WHERE kl.company_id = ? ';
        $strCount = 'SELECT count(*) as cnt FROM ec_kyc_log kl WHERE kl.company_id = ? ';
        $report = 'SELECT count(*) as cnt FROM ec_kyc_log kl WHERE kl.company_id = ? ';
        $lineReport = DB::select('SELECT count(*) AS total, MONTH(created_at) AS month FROM ec_kyc_log WHERE company_id = ? AND code = 200 AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)', array($user->company_id));
        $arr = array($user->company_id);


        if ($searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND kl.created_at >= ? AND kl.created_at <= ?';
            $report .= ' AND kl.created_at >= ? AND kl.created_at <= ?';
            $strCount .= ' AND kl.created_at >= ? AND kl.created_at <= ?';
            array_push($arr, $searchData["startDate"]);
            array_push($arr, $searchData["endDate"]);
        } else if ($searchData["start_date"] && !$searchData['end_date']) {
            $str .= ' AND kl.created_at >= ? ';
            $report .= ' AND kl.created_at >= ? ';
            $strCount .= ' AND kl.created_at >= ? ';
            array_push($arr, $searchData["startDate"]);
        } else if (!$searchData["start_date"] && $searchData['end_date']) {
            $str .= ' AND kl.created_at <= ?';
            $report .= ' AND kl.created_at >= ? ';
            $strCount .= ' AND kl.created_at <= ?';
            array_push($arr, $searchData["endDate"]);
        }

        $kyc_success = $report . ' AND kl.code = 200';
        $kyc_failed = $report . ' AND kl.code != 200';

        $total_kyc_success = DB::select($kyc_success, $arr);
        $total_kyc_failed = DB::select($kyc_failed, $arr);

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res, "total_kyc_success" => $total_kyc_success[0]->cnt, "total_kyc_failed" => $total_kyc_failed[0]->cnt , "line_report" => $lineReport);
    }

    public function exportSignEkyc($startDate, $endDate, $keyword)
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_SIGN_EKYC");
        if (!$permission || $permission->is_view != 1) throw new eCAuthenticationException();

        $diff = date_diff(date_create($startDate), date_create($endDate));
        $dateOver = intval($diff->format("%R%a"));
        if ($dateOver < 0) throw new eCBusinessException('REPORT.ERROR_START_DATE_SMALLER_END_DATE');
        if ($dateOver > 30) throw new eCBusinessException('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS');

        $str = 'SELECT kl.id, kl.code, kl.type, kl.created_at, kl.start_time, kl.end_time FROM ec_kyc_log kl  WHERE kl.company_id = ? ';
        $arr = array($user->company_id);

        if ($startDate && $endDate) {
            $str .= ' AND kl.created_at >= ? AND kl.created_at <= ?';
            array_push($arr, $startDate);
            array_push($arr, $endDate);
        } else if ($startDate && !$endDate) {
            $str .= ' AND kl.created_at >= ? ';
            array_push($arr, $startDate);
        } else if (!$startDate && $endDate) {
            $str .= ' AND kl.created_at <= ? ';
            array_push($arr, $endDate);
        }

        $records = DB::select($str, $arr);

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Báo cáo kyc');

        // Tạo header
        $activeSheet->setCellValue('A1', 'Thời gian gửi');
        $activeSheet->setCellValue('B1', 'Loại');
        $activeSheet->setCellValue('C1', 'Mã request');
        $activeSheet->setCellValue('D1', 'Trạng thái');
        $activeSheet->setCellValue('E1', 'Tốc độ');

        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'E') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }
        // Điền dữ liệu
        $i = 2;
        foreach ($records as $record) {
            $status = "";
            $type = "";

            if ($record->code == 200) {
                $status = 'Thành công';
            } else {
                $status = 'Thất bại';
            }

            if ($record->type == 1) {
                $type = "Check ảnh";
            } else if ($record->type == 2) {
                $type = "Bóc tách";
            } else if ($record->type == 3) {
                $type = "Verify";
            }

            $activeSheet->setCellValue('A' . $i, $record->created_at);
            $activeSheet->setCellValue('B' . $i, $type );
            $activeSheet->setCellValue('C' . $i, $record->code);
            $activeSheet->setCellValue('D' . $i, $status);
            $activeSheet->setCellValue('E' . $i, ($record->end_time - $record->start_time) ."ms");
            $i++;
        }
        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }

    public function initSignAssignee()
    {
        $user = Auth::user();
        $permission = $this->permissionService->getPermission($user->role_id, "REPORT_SIGN_ASSIGNEE");
        if (!$permission || $permission->is_view != 1) {
            throw new eCAuthenticationException();
        }

        $lstDocumentGroup = eCDocumentGroups::where('status', 1)->get();
        $lstDocumentType = eCDocumentTypes::where('company_id', $user->company_id)->where('status', 1)->get();

        return array('permission' => $permission, 'lstDocumentType' => $lstDocumentType, 'lstDocumentGroup' => $lstDocumentGroup);
    }

    public function searchSignAssignee($searchData, $draw, $start, $limit, $sortQuery){
        $user = Auth::user();
        $arr = array();

        $str = 'SELECT a.company_id, a.full_name, a.email, a.phone, a.national_id, a.address, a.submit_time, a.national_id, d.id as doc_id, d.name as doc_name, d.code, d.source, p.company_name FROM ec_document_assignees as a JOIN ec_documents as d ON a.document_id = d.id JOIN ec_document_partners as p ON a.document_id  = p.document_id  WHERE d.delete_flag = 0 AND a.state = 2 AND a.assign_type = 2 ' ;
        $strCount = 'SELECT count(*) as cnt FROM ec_document_assignees as a JOIN ec_documents as d ON a.document_id = d.id JOIN ec_document_partners as p ON a.document_id  = p.document_id  WHERE d.delete_flag = 0 AND a.state = 2 AND a.assign_type = 2 ';
        if($searchData["source"] != "-1")	{
            $str .= ' AND d.source = ?';
            $strCount .= ' AND d.source = ?';
            array_push($arr, $searchData["source"]);
        }

        if (!empty($searchData["keyword"])) {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ? OR p.company_name LIKE ?)';
            $strCount .= ' AND (d.name LIKE ? OR d.code LIKE ? OR p.company_name LIKE ?)';
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
            array_push($arr, '%' . $searchData["keyword"] . '%');
        }

        if ($searchData["document_group_id"] != -1) {
            $str .= ' AND d.document_type = ?';
            $strCount .= ' AND d.document_type = ?';
            array_push($arr, $searchData["document_group_id"]);
        }

        if ($searchData['type'] == 0) {
            $str .= ' AND p.organisation_type = ? ';
            $strCount .= ' AND p.organisation_type = ? ';
            array_push($arr, 1);
        } else if ($searchData['type'] == 1) {
            $str .= ' AND p.organisation_type != ? ';
            $strCount .= ' AND p.organisation_type != ? ';
            array_push($arr, 1);
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

        $str .= " ORDER BY " . $sortQuery;

        $str .= " LIMIT ? OFFSET  ? ";
        array_push($arr, $limit, $start);

        $res = DB::select($str, $arr);
        $resCount = DB::select($strCount, $arr);

        return array("draw" => $draw, "recordsTotal" => $resCount[0]->cnt, "recordsFiltered" => $resCount[0]->cnt, "data" => $res);
    }

    public function exportSignAssignee($startDate, $endDate, $document_group_id, $source, $keyword, $type){
        $user = Auth::user();
        $arr = array();

        $str = 'SELECT a.company_id, a.full_name, a.email, a.phone, a.national_id, a.address, a.submit_time, a.national_id, d.id as doc_id, d.name as doc_name, d.code, d.source, p.company_name FROM ec_document_assignees as a JOIN ec_documents as d ON a.document_id = d.id JOIN ec_document_partners as p ON a.document_id = p.document_id  WHERE d.delete_flag = 0 AND a.state = 2 AND a.assign_type = 2 ' ;
        if($source != -1)	{
            $str .= ' AND d.source = ?';
            array_push($arr, $source);
        }

        if ($keyword != 'undefined') {
            $str .= ' AND (d.name LIKE ? OR d.code LIKE ? OR p.company_name LIKE ?)';
            array_push($arr, '%' . $keyword . '%');
            array_push($arr, '%' . $keyword . '%');
            array_push($arr, '%' . $keyword . '%');
        }

        if ($document_group_id != -1) {
            $str .= ' AND d.document_type = ?';
            array_push($arr, $document_group_id);
        }

        if ($type == 0) {
            $str .= ' AND p.organisation_type = ? ';
            array_push($arr, 1);
        } else if ($type == 1) {
            $str .= ' AND p.organisation_type != ? ';
            array_push($arr, 1);
        }

        if ($startDate && $endDate) {
            $str .= ' AND d.sent_date >= ? AND d.sent_date <= ?';
            array_push($arr, $startDate);
            array_push($arr, $endDate);
        } else if ($startDate && !$endDate) {
            $str .= ' AND d.sent_date >= ? ';
            array_push($arr, $startDate);
        } else if (!$startDate && $endDate) {
            $str .= ' AND d.sent_date <= ?';
            array_push($arr, $endDate);
        }
        $records = DB::select($str, $arr);

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Chi tiết dánh sách người ký');

        // Tạo header
        $activeSheet->setCellValue('A1', 'Công ty');
        $activeSheet->setCellValue('B1', 'Tên người ký');
        $activeSheet->setCellValue('C1', 'Email');
        $activeSheet->setCellValue('D1', 'Số CMND/CCCD');
        $activeSheet->setCellValue('E1', 'số điện thoại');
        $activeSheet->setCellValue('F1', 'Mã số tài liệu');
        $activeSheet->setCellValue('G1', 'Tên tài liệu');
        $activeSheet->setCellValue('H1', 'Mỗi trường ký');
        $activeSheet->setCellValue('I1', 'Ngày ký');


        // Căn chỉnh độ rộng của cột
        foreach (range('A', 'I') as $columnId) {
            $activeSheet->getColumnDimension($columnId)->setAutoSize(true);
        }
        // Điền dữ liệu
        $i = 2;
        foreach ($records as $record) {

            $env = "";

            if ($record->source == 0) {
                $env = 'Web';
            } else if ($record->source == 1) {
                $env = 'Api';
            } else{
                return;
            }

            $activeSheet->setCellValue('A' . $i, $record->company_name);
            $activeSheet->setCellValue('B' . $i, $record->full_name);
            $activeSheet->setCellValue('C' . $i, $record->email);
            $activeSheet->setCellValue('D' . $i, $record->national_id);
            $activeSheet->setCellValue('E' . $i, $record->phone);
            $activeSheet->setCellValue('F' . $i, $record->code);
            $activeSheet->setCellValue('G' . $i, $record->doc_name);
            $activeSheet->setCellValue('H' . $i, $env);
            $activeSheet->setCellValue('I' . $i, $record->submit_time);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        return $writer->save('php://output');
    }
}
