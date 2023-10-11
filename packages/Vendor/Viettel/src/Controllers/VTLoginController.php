<?php

namespace Viettel\Controllers;

use Carbon\Carbon;
use Core\Controllers\eCBaseController;
use Core\Helpers\ApiHttpStatus;
use Core\Messages\ApiMessages;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VTLoginController extends eCBaseController
{
    public function login(Request $request)
    {
        try {
            $username = $request->json("username");
            $password = $request->json("password");
            if (strlen($password) < 6)   return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::PASSWORD_SHORT, array(), ApiHttpStatus::VALIDATION);
            $rules = [
                'username' => 'required',
                'password' => 'required|min:6'
            ];
            $validator = Validator::make(['username' => $username, 'password' => $password], $rules);
            if ($validator->fails()) {
                return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::VALIDATE_FAILED, array(), ApiHttpStatus::BAD_REQUEST);
            }
            $credentials = ['username' => $username, 'password' => $password];
            if (!$token = auth('vendor')->attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp])) {
                return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::AUTH_FAILED, array(), ApiHttpStatus::PASSWORD_WRONG);
            }

            $user = auth('vendor')->user();
            if ($user->revoked == 1) {
                return $this->sendError(ApiHttpStatus::VALIDATION, ApiMessages::ACCOUNT_NOT_ACCEPTABLE, array(), ApiHttpStatus::NOT_ACCEPTABLE, ApiHttpStatus::OK);
            }

            //Step 2 : login
            $user['token'] = $token;

            //update last login
            $results = [
                'token' => $user->token,
                'expires_in' => auth('vendor')->factory()->getTTL() * 60
            ];

            return $this->sendResponse($results, ApiMessages::AUTH_SUCCESS);
        } catch (Exception $e) {
            Log::error("[LoginController][login] cause:  " . $e->getMessage() . ' line: ' . $e->getLine());
            return $this->sendError(ApiHttpStatus::INTERNAL_SERVER_ERROR, ApiMessages::ERROR_PROCESSING, $e->getMessage(), ApiHttpStatus::BAD_REQUEST);
        }
    }
}
