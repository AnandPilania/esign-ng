<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxFile implements Rule
{
    public $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
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
        if(is_array($value) && count($value) > 1) {
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
