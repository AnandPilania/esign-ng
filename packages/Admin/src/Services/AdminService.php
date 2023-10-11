<?php


namespace Admin\Services;

use Core\Services\eContractBaseService;
use Illuminate\Support\Facades\DB;

class AdminService extends eContractBaseService
{

    /**
     * AdminService constructor.
     */
    public function __construct()
    {
        parent::__construct(AdminService::class);
    }

    public function updateAccessToken($user)
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'sex' => $user->sex,
            'status' => $user->status,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'token_type' => 'bearer',
            'language' => $user->language,
            'token' => $user->token,
            'expires_in' => auth('jwt')->factory()->getTTL() * 60
        ];
    }

    public function getUserInfo($user) {
        return [
            'id' => $user->id,
            'address' => $user->address,
            'name' => $user->full_name,
            'phone' => $user->phone,
            'email' => $user->email,
            'sex' => $user->sex .'',
            'language' => $user->language,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'role_id' => $user->role_id,
            'agency_id' => $user->agency_id
        ];
    }
}
