<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class ByteRule implements Rule
{
    public $message;
    public $maxByte;

    /**
     * Create a new rule instance.
     *
     * @param $message
     * @param $maxByte
     */
    public function __construct($message, $maxByte)
    {
        $this->message = $message;
        $this->maxByte = $maxByte;
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
        $len = mb_strlen($value, 'UTF-8');
        $maxByte = 0;
        for ($i = 0; $i < $len; $i++) {
            $characters = mb_substr($value, $i, 1, 'UTF-8');
            if (preg_match('/[\\\u3000-\\\u303F]+|[一-龠]+|[ぁ-ゔ]+|[ァ-ヴー]+|[ａ-ｚＡ-Ｚ０-９]+|[々〆〤]+/u', $characters) && !preg_match('/[0-9]+/u', $characters)) {
                $maxByte += 2;
            }else{
                $maxByte += 1;
            }
        }
        if($maxByte > $this->maxByte){
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
