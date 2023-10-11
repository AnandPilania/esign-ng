<?php


namespace Core\Controllers;


use Core\Helpers\ApiHttpStatus;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Customer\Exceptions\eCAuthenticationException;
use Customer\Exceptions\eCBusinessException;

class eCBaseController
{
    public function sendResponse($result, $message, $code = ApiHttpStatus::OK)
    {
        return Response::json(self::makeResponse($message, $result), $code);
    }

    public function sendError($err_code, $message, $error = [], $code = ApiHttpStatus::BAD_REQUEST)
    {
        return Response::json(self::makeError($err_code, $message, $error), $code);
    }


    public function makeResponse($message, $data)
    {
        $res = [
            'status' => 200,
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        return $res;
    }
    public static function makeError($err_code, $message, $error)
    {
        $res = [
            'status' => $err_code,
            'success' => false,
            'message' => $message,
        ];
        if (!empty($error)) {
            $res['error'] = $error;
        }

        return $res;
    }

    public function handleException($error)
    {
        if ($error instanceof eCAuthenticationException)
            return $this->sendError(ApiHttpStatus::UNAUTHORIZED, 'SERVER.NOT_PERMISSION', [], ApiHttpStatus::UNAUTHORIZED);
        if ($error instanceof eCBusinessException)
            return $this->sendError(ApiHttpStatus::BAD_REQUEST, $error->getMessage(), [], ApiHttpStatus::BAD_REQUEST);
        return $this->sendError(ApiHttpStatus::BAD_REQUEST, "SERVER.PROCESSING_ERROR", $error->getMessage(), ApiHttpStatus::BAD_REQUEST);
    }

    public function getPermission($role, $func)
    {
        $permissions = DB::select("SELECT rp.* FROM ec_s_role_permission rp JOIN ec_s_permissions p ON rp.permission_id = p.id WHERE p.permission = ? AND rp.role_id = ? and p.status = 1", array($func, $role));
        if (count($permissions) > 0) {
            return $permissions[0];
        }
        return null;
    }

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
}
