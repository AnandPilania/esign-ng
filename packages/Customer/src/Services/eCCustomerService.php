<?php


namespace Customer\Services;

use Core\Helpers\HistoryActionGroup;
use Core\Helpers\HistoryActionType;
use Core\Models\eCOauthAccessTokens;
use Core\Models\eCRole;
use Core\Models\eCUser;
use Core\Models\eCUserSignature;
use Core\Services\eContractBaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Core\Services\ActionHistoryService;
use Core\Helpers\ImageHelper;
use Illuminate\Support\Facades\Log;

class eCCustomerService extends eContractBaseService
{
    private $actionHistoryService;
    private $imageHelper;

    public function __construct(ActionHistoryService $actionHistoryService, ImageHelper $imageHelper)
    {
        $this->actionHistoryService = $actionHistoryService;
        $this->imageHelper = $imageHelper;
    }

    public function updateAccessToken($user)
    {
        $oatTmp = eCOauthAccessTokens::where(['user_id' => $user->id])->orderBy('created_at', 'DESC')->first();
        if (isset($oatTmp)) {
            $oat_id = $oatTmp->id;

            $oat = new eCOauthAccessTokens();
            $oat->id = $oat_id;
            $oat->user_id = $oatTmp->user_id;
            $oat->client_id = $oatTmp->client_id;
            $oat->name = $oatTmp->name;
            $oat->scopes = $oatTmp->scopes;
            $oat->revoked = $oatTmp->revoked;
            $oat->created_at = $oatTmp->created_at;
            $oat->updated_at = $oatTmp->updated_at;
            $oat->expires_at = $oatTmp->expires_at;

            eCOauthAccessTokens::where('user_id', '=', $user->id)->delete();

            $oat->save();
                    }
        return [
            'account' => $user->id,
            'full_name' => $user->name,
            'phone' => $user->phone,
            'sex' => $user->sex,
            'status' => $user->status,
            'language' => $user->language,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'token' => $user->token,
            'is_first_login' => $user->is_first_login,
        ];
    }

    public function getUserInfo($user) {
        $role = eCRole::find($user->role_id);
        return [
            'id' => $user->id,
            'company_id' => $user->company_id,
            'address' => $user->address,
            'name' => $user->name,
            'phone' => $user->phone,
            'branch_id' => $user->branch_id,
            'email' => $user->email,
            'sex' => $user->sex .'',
            'language' => $user->language,
            'dob' => date("d/m/Y", strtotime($user->dob)),
            'role_id' => $user->role_id,
            'role_name' => $role->role_name,
            'is_first_login' => $user->is_first_login
        ];
    }

    public function updateUserSignature($userSignature)
    {
        $user = Auth::user();

        $userSignature = $this->imageHelper->resizeImage($userSignature, 300,200);

        $signature = eCUserSignature::where('user_id', $user->id)->first();
        if (!$signature) {
            $signature = new eCUserSignature();
            $signature->user_id = $user->id;
            $signature->image_signature = $userSignature;
            $signature->save();
        } else {
            eCUserSignature::where('user_id', $user->id)
                ->update([
                    'image_signature' => $userSignature,
                ]);
        }
        return true;
    }
    public function deleteUserSignature()
    {
        $user = Auth::user();
        $signature = eCUserSignature::where('user_id', $user->id)->first();

        if (!$signature) {
            $message = '';
            // throw new eCBusinessException("CONFIG.ACCOUNT.NOT_EXISTED_SIGNATURE");
        } else {
            $message = 'SERVER.DELETE_SUCCESSFUL';
            eCUserSignature::where('user_id', $user->id)->delete();
            $this->actionHistoryService->SetActivity(HistoryActionType::WEB_ACTION,$user,HistoryActionGroup::USER_ACTION,'ec_user_signature', 'UPDATE_USER_SIGNATURE',$user->name, 1);
        }
        return array('message' => $message);
    }

    public function updateFirstLogin($user){
        $user = eCUser::find($user->id);
        $user->update(['is_first_login' => false]);
        return true;
    }
}
