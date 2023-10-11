<?php


namespace Report\ReportFetcher;


use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

abstract class CSVAbstract
{
    protected $base_path;
    protected $objPHPExcel;
    protected $activeSheet;
    protected $objWriter;
    protected $type;
    protected $is_encrypt;

    const CSV = 1;
    const XLS = 2;
    const XLSX = 3;

    /**
     * CSVAbstract constructor.
     * @param $template
     * @param string $encoding
     */
    public function __construct($template, $encoding = 'SJIS')
    {
        $this->initSetting($template, $encoding);
        $this->is_encrypt = (bool) env('IS_ENCRYPT', true);
    }

    protected function initSetting($template, $encoding = 'SJIS')
    {
        $this->base_path = dirname(dirname(__FILE__));
        $report_template = $this->base_path . '/Templates/' . $template;
        $extension = pathinfo($report_template, PATHINFO_EXTENSION);
        if ($extension == 'csv') {
            $objReader = new Csv();
            $this->type = self::CSV;
            $objReader->setInputEncoding($encoding);
        } elseif ($extension == 'xlsx') {
            $objReader = new Xlsx();
            $this->type = self::XLSX;
        } else {
            $this->type = self::XLS;
            $objReader = new Xls();
        }

        $this->objPHPExcel = $objReader->load($report_template);

        $this->init(0);
    }

    protected function writerReport()
    {
        switch ($this->type) {
            case self::CSV:
                $this->objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($this->objPHPExcel);
                $this->objWriter->setEnclosure('');
                $this->objWriter->setUseBOM(true);
                break;
            case self::XLSX:
                $this->objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
                break;
            case self::XLS:
                $this->objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xls($this->objPHPExcel);
                break;
            default:
                throw new \Exception('Not support this file extension');
        }

        return $this->objWriter;
    }

    protected function init($sheet_index = 0)
    {
        try {
            $this->activeSheet = $this->objPHPExcel->setActiveSheetIndex($sheet_index);
        } catch (Exception $e) {
            $this->activeSheet = null;
        }
    }

    protected function convert($fields)
    {
        $result = [];
        foreach ($fields as $field) {
            $result[] = mb_convert_encoding($field, 'sjis-win', 'UTF-8');
        }
        return $result;
    }

    protected function arr2csv($rows)
    {
        $fp = fopen('php://memory', 'r+b');
        foreach ($rows as $fields) {
            // Convert row data from UTF-8 to Shift-JS
            $fields = $this->convert($fields);
            fputcsv($fp, $fields);
        }
        rewind($fp);
        // Convert CRLF
        $tmp = stream_get_contents($fp);
        fclose($fp);

        return $tmp;
    }

    abstract protected function exportReport($inputs = []);

    abstract function writeCsv($inputs = []);
}
