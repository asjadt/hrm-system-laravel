<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SomeTimes implements Rule
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

      return  collect($value)->contains(function ($data, $key) {
            return ($data["checked"] == true || $data["checked"] == 1);


        });
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please select at least one';
    }
}
