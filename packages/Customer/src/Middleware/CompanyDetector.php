<?php


namespace Customer\Middleware;

use Closure;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Customer\Services\eCCompanyService;

class CompanyDetector
{
    private $eCompanyService;

    private $baseApi;

    public function __construct(eCBaseController $baseApi, eCCompanyService $eCCompanyService)
    {
        $this->baseApi = $baseApi;
        $this->eCompanyService = $eCCompanyService;
    }

    public function handle($request, Closure $next)
    {

        $companyId = $request->header('company-id');

        if (empty($companyId)) {
            return $this->baseApi->sendError('Missing request param: company-id', ApiHttpStatus::BAD_REQUEST);
        }
        $company = $this->eCompanyService->getCompany($companyId);
        if (empty($company))
            return $this->baseApi->sendError('Not found the company in the system', ApiHttpStatus::BAD_REQUEST);

        //TODO: Need to check status && state
        $request->attributes->add(['company' => $company]);
        return $next($request);
    }
}
