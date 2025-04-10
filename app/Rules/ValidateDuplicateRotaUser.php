<?php

namespace App\Rules;

use App\Models\UserEmployeeRota;
use Illuminate\Contracts\Validation\Rule;

class ValidateDuplicateRotaUser implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

     protected $id;
     public function __construct($id)
     {
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
        $userEmployeeRota =  UserEmployeeRota::where([
            "user_id" => $value
        ])
        ->when(!empty($this->id), function($query) use($value) {
            $query->whereNotIn("user_id",[$value]);
        })
        ->first();

       return empty($userEmployeeRota)?1:0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is already taken.';
    }
}
