<?php


namespace Customer\Services;


use Core\Models\eCCompany;
use Core\Services\eContractBaseService;

class eCCompanyService extends eContractBaseService
{
    public function getCompany($company_id)
    {
        return eCCompany::find($company_id);
    }
}
