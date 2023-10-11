<?php


namespace Report\Reader;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use SplFileObject;

abstract class CSVReaderAbstract
{
    protected $reader;

    protected $is_decrypt = false;

    protected $is_migrate = true;

    public function __construct()
    {
    }

    protected function initSetting($file_tmp)
    {
        $extension = pathinfo($file_tmp, PATHINFO_EXTENSION);
        if ($extension == 'csv') {
            $this->reader = new Csv();
            $this->reader->setDelimiter(';');
            $this->reader->setEnclosure('');
            $this->reader->setSheetIndex(0);
        } elseif ($extension == 'xlsx') {
            $this->reader = new Xlsx();
        } else {
            $this->reader = new Xls();
        }
    }

    abstract protected function readFile($file_tmp);

    protected function get_csv($csvfile, $mode = 'sjis')
    {
        // ファイル存在確認
        if (!file_exists($csvfile)) return false;

        // 文字コードを変換しながら読み込めるようにPHPフィルタを定義
        if ($mode === 'sjis') $filter = 'php://filter/read=convert.iconv.cp932%2Futf-8/resource=' . $csvfile;
        else if ($mode === 'utf16') $filter = 'php://filter/read=convert.iconv.utf-16%2Futf-8/resource=' . $csvfile;
        else if ($mode === 'utf8') $filter = $csvfile;

        // SplFileObject()を使用してCSVロード
        $file = new SplFileObject($filter);
        if ($mode === 'utf16') $file->setCsvControl("\t");
        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::READ_AHEAD
        );

        // 各行を処理
        $records = array();
        foreach ($file as $i => $row) {
            // 1行目はキーヘッダ行として取り込み
            if ($i === 0) {
                foreach ($row as $j => $col) $colbook[$j] = $j;
                continue;
            }

            // 2行目以降はデータ行として取り込み
            $line = array();
            foreach ($colbook as $j => $col)
                $line[$colbook[$j]] = @$row[$j];
            $records[] = $line;
        }

        return $records;
    }

    public function decrypt($ivHashCiphertext)
    {
        $rs = openssl_decrypt($ivHashCiphertext, 'aes-256-cbc', AES_SECRET_KEY, 0, AES_IV);
        return mb_convert_encoding($rs, "sjis-win","UTF-16LE");
    }

    public function setDecrypt($encrypt)
    {
        $this->is_decrypt = $encrypt;
    }

    protected function setMigrate($is_migrate)
    {
        $this->is_migrate = $is_migrate;
    }

}
