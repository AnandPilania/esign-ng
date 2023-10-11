<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Date: 11/25/18
 * Time: 1:26 AM
 */

namespace Admin\Listeners;


use Exception;
use Admin\Events\UpdateRoleEvent;
use Admin\Services\RoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class UpdateRoleListener implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $role_service;
    /**
     * PTAccountLoginListener constructor.
     */
    public function __construct()
    {
        $this->role_service = new RoleService();
    }

    /**
     * @param UpdateRoleEvent $event
     * @return string
     */
    public function handle(UpdateRoleEvent $event)
    {
        try {
            Log::info("[UpdateRoleListener][handle] : need to load role cache");
            $user_id = $event->user_id;
            $role_id = $event->role_id;
            $scope = $event->scope;
            $results = $this->role_service->getRoles($role_id);
            $data = [];
            $role_scope = "ROLE::$role_id";
            if (isset($user_id) && $user_id > 0) {
                $key = "SCOPE::$scope::$user_id";
                Log::info("Key =  $key");
                $data[$key] = $role_scope;
            }
            $data[$role_scope] = $results;
            session($data);
        } catch (Exception $ex) {
            Log::error("[UpdateRoleListener][handle] " . $ex->getMessage());
            return $ex->getMessage();
        }
        return "";
    }
}
