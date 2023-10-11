<?php


namespace Report\Pdf;

use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class PdfExport
{

    private $pdfName;

    /**
     * PdfExport constructor.
     * @param $pdfName
     */
    public function __construct($pdfName)
    {
        $this->pdfName = $pdfName;
    }

    public function exportPdf($view, $data, $encoding = 'SJIS')
    {
        Log::info("[exportPdf] : begin export PDF");

        $pdf = MPDF::loadView($view,  $data);

        $pdf->save($this->pdfName);
        $pdf_path = public_path() .'/'. $this->pdfName;

        return response()->download($pdf_path);
    }

    public function previewPdf($view, $data, $encoding = 'SJIS')
    {
        Log::info("[exportPdf] : begin export PDF");

        $pdf = MPDF::loadView($view,  $data);

        return $pdf->stream($this->pdfName);
    }

    public function exportTmpPdf($view, $data, $encoding = 'SJIS')
    {
        Log::info("[exportTmpPdf] : begin export PDF");

        $pdf = MPDF::loadView($view,  $data);

        $pdf->save($this->pdfName);
        return public_path() .'/'. $this->pdfName;
    }
}
