<?php

namespace Core\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class CheckBirthDay implements Rule
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
        if($value == Date('Y-m-d H:i:s', strtotime($value))){
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
        return $this->message;
    }
}
