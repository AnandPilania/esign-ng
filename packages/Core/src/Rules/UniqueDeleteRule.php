<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueDeleteRule implements Rule
{
    public $model;
    public $message;
    public $id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($model, $message, $id = null)
    {
        $this->model = $model;
        $this->message = $message;
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($this->id){
            $data = $this->model->where($attribute, $value)->where('delete_flag', false)->where('id', '<>', $this->id)->count();
        }else{
            $data = $this->model->where($attribute, $value)->where('delete_flag', false)->count();
        }
        if($data){
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
