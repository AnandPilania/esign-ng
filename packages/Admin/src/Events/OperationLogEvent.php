<?php


namespace Admin\Events;


class OperationLogEvent
{
    public $scope;
    public $user_id;
    public $email;
    public $uri;
    public $query;
    public $ip;

    /**
     * OperationLogEvent constructor.
     * @param $scope
     * @param $user_id
     * @param $email
     * @param $uri
     * @param $query
     * @param $ip
     */

    public function __construct($scope, $user_id, $email, $uri, $query, $ip)
    {
        $this->scope = $scope;
        $this->user_id = $user_id;
        $this->email = $email;
        $this->uri = $uri;
        $this->query = $query;
        $this->ip = $ip;
    }

}
