<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Time: 1:25 AM
 */

namespace Admin\Events;


class UpdateRoleEvent
{

    public $user_id;

    public $role_id;

    public $scope;

    /**
     * PTAccountLoginEvent constructor.
     * @param $user_id
     * @param $role_id
     * @param $scope
     */
    public function __construct($user_id, $role_id, $scope)
    {
        $this->user_id = $user_id;
        $this->role_id = $role_id;
        $this->scope = $scope;
    }
}
