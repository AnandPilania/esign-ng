<?php


namespace Customer\Exceptions;


use Core\Helpers\ApiHttpStatus;

class eCAuthenticationException extends \Exception
{

    /**
     * eCAuthenticationException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message = 'You do not have permission for this action',
                                $code = ApiHttpStatus::FORBIDDEN)
    {
        parent::__construct($message, $code);
    }
}
