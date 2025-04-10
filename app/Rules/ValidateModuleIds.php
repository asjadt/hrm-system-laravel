<?php

namespace App\Rules;

use App\Models\Module;
use Illuminate\Contracts\Validation\Rule;

class ValidateModuleIds implements Rule
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

        $module = Module::
        where('id', $value)
      ->where('modules.is_enabled', 1)
      ->first();

      if(empty($module)) {
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
       return 'The :attribute is invalid.';
    }
}
