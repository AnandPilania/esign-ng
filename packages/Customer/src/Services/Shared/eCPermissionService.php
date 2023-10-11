<?php


namespace Customer\Services\Shared;


use Carbon\Carbon;
use Core\Models\eCCompany;
use Core\Models\eCDocuments;
use Core\Models\eCService;
use Customer\Exceptions\eCBusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class eCPermissionService
{
    public function getPermission($role, $func)
    {
        $permissions = DB::select("SELECT rp.* FROM ec_s_role_permission rp JOIN ec_s_permissions p ON rp.permission_id = p.id WHERE p.permission = ? AND rp.role_id = ? and p.status = 1", array($func, $role));
        if (count($permissions) > 0) {
            return $permissions[0];
        }
        return null;
    }

    //TODO: Should use gate policy to do.
    public function checkPermission($role, $func, $view = false, $edit = false, $approve = false, $decision = false)
    {
        $str = "SELECT * FROM ec_s_role_permission rp JOIN ec_s_permissions p ON rp.permission_id = p.id WHERE p.permission = ? AND rp.role_id = ? and p.status = 1 ";
        $params = array($func, $role);

        if ($view) {
            $str .= " AND rp.is_view = 1";
        }
        if ($edit) {
            $str .= " AND rp.is_write = 1";
        }
        if ($approve) {
            $str .= " AND rp.is_approval = 1";
        }
        if ($decision) {
            $str .= " AND rp.is_decision = 1";
        }

        $res = DB::select($str, $params);
        if (count($res) > 0) {
            return true;
        }
        return false;
    }

    public function checkExpired($company_id) {
        $company = eCCompany::select('total_doc', 'expired_date', 'service_id')->where('id', $company_id)->first();
        $serviceConfig = eCService::find($company->service_id);
        if ($serviceConfig->quantity < $company->total_doc){
            throw new eCBusinessException("SERVER.SERVICE_QUANTITY_EXPIRED");
        }
        if (isset($company->expired_date)) {
            $expiresTime = Carbon::parse($company->expired_date);
            if (!$expiresTime->gt(Carbon::now())){
                throw new eCBusinessException("SERVER.SERVICE_EXPIRED");
            }
        }
       try {
           DB::beginTransaction();
            $eCompany = eCCompany::find($company_id);
            $eCompany->total_doc++;
            $eCompany->save();
            DB::commit();
            return true;
       } catch (\Exception $e) {
            DB::rollBack();
            return false;
       }
    }
}
