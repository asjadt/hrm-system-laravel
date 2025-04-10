<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TimeValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        
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

            $timeParts = explode(':', $value);
            if(empty($timeParts[0]) || empty($timeParts[1])) {
                return false;
            }
            $hour = $timeParts[0];
            $minute = $timeParts[1];
            $second = !empty($timeParts[2])?$timeParts[2]:0;
            if (!checkdate(1, 1, 1) || !checkdate(1, 1, 1970) || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59) {
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
        return 'The :attribute field must be a valid time value.';
    }
}
