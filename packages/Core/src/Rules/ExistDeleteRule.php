<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class ExistDeleteRule implements Rule
{
    public $model;
    public $message;
    public $attribute;

    /**
     * Create a new rule instance.
     *
     * @param $model
     * @param $message
     * @param null $attribute
     */
    public function __construct($model, $message = null, $attribute = null)
    {
        $this->model = $model;
        $this->message = $message;
        $this->attribute = $attribute;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->attribute) {
            $data = $this->model->where($this->attribute, $value)->where('delete_flag', false)->count();
        } else {
            $data = $this->model->where($attribute, $value)->where('delete_flag', false)->count();
        }
        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->message) {
            return $this->message;
        }
        return 'The :attribute does not exist.';
    }
}
