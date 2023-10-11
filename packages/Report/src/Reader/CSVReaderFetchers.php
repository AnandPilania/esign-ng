<?php


namespace Report\Reader;


use Illuminate\Support\Facades\Log;

class CSVReaderFetchers
{
    protected $fetchers;

    /**
     * ReportFetchers constructor.
     */
    public function __construct()
    {
        $this->fetchers = $this->initFetcher();
    }

    private function initFetcher()
    {
        return array(

        );
    }

    public function getFetcher($report_type)
    {
        Log::info("CSV: " . $report_type);
        if (array_key_exists($report_type, $this->fetchers))
            return $this->fetchers[$report_type];
        return null;
    }

    public function addFetcher($report_type, $clazz)
    {
        $this->fetchers[] = [
            $report_type => $clazz
        ];
    }
}
