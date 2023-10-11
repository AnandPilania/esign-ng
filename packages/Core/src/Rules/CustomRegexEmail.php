<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomRegexEmail implements Rule
{
    /**
     * @var string
     */
    public $message;

    /**
     * CustomRegexEmail constructor.
     * @param null $message
     */
    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        } else {
            $this->message = 'Email format is incorrect';
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return $this->message;
    }
}
