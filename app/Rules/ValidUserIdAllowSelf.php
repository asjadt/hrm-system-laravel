<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ValidUserIdAllowSelf implements Rule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $all_manager_department_ids;
    private $customMessage;

    public function __construct($all_manager_department_ids)
    {
        $this->all_manager_department_ids = $all_manager_department_ids;
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



        $user = User::where([
            'users.id' => $value,
            'users.business_id' => auth()->user()->business_id,
            "is_active" => 1
        ])
        ->where(function($query){
            $query->whereHas("departments", function($query) {
                $query->whereIn("departments.id",$this->all_manager_department_ids);
            })
            ->orWhere([
                "id" => auth()->user()->id
            ]);
        })


        ->first();


        return $user?1:0;
    }

    public function message()
    {
       return $this->customMessage ?: 'The :attribute is invalid.';
    }



}
