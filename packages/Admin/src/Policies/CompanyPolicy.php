<?php


namespace Admin\Policies;


use Core\Traits\AbstractPolicy;

class CompanyPolicy
{
    use AbstractPolicy;

    public function __construct()
    {
        $this->setModel('company');
    }
}
