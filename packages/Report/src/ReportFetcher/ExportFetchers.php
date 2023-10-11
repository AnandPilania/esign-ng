<?php

namespace Report\ReportFetcher;

use Illuminate\Support\Facades\Log;

class ExportFetchers
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
        return [

        ];
    }

    public function getFetcher($report_type)
    {
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
