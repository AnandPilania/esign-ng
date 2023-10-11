<?php


namespace Core\Traits;


trait AbstractPolicy
{
    protected $model;

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    public function read($guard)
    {
        return ($guard && $guard->hasRead($this->model));
    }

    public function write($guard)
    {
        return ($guard && $guard->hasWrite($this->model));
    }
}
