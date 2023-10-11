<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckOutTime implements Rule
{
    /**
     * column start
     * @var string
     */
    public $start;
    /**
     * column end
     * @var string
     */
    public $end;
    /**
     * check time out
     * @var int
     */
    public $timeOut;
    public $message;

    /**
     * Create a new rule instance.
     *
     * @param null $message
     * @param string $start
     * @param string $end
     * @param int $timeOut
     */
    public function __construct($message = null, $start = 'start_at', $end = 'end_at', $timeOut = 1)
    {
        $this->start = $start;
        $this->end = $end;
        $this->timeOut = $timeOut;
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
        $start = date('Y-m-d', strtotime(request($this->start)));
        $end = date('Y-m-d', strtotime(request($this->end)));
        $interval = date_diff(date_create($start), date_create($end));
        return  ($interval->y < $this->timeOut) || ($interval->y == $this->timeOut && $interval->m == ZERO_NUMBER && $interval->d == ZERO_NUMBER);
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
        } else {
            return trans('admin/validation.analyse.ordersale.start_at.max');
        }
    }
}
