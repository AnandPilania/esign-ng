<?php


namespace Customer\Exceptions;


use Core\Helpers\ApiHttpStatus;

class eCBusinessException extends \Exception
{

    public function __construct($message, $code = ApiHttpStatus::BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }
}
