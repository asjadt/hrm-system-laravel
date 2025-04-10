<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ValidUserId implements Rule
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

        $authUser = auth()->user();

        $user = User::where([
            'users.id' => $value,
            'users.business_id' => auth()->user()->business_id,
            "is_active" => 1
        ])
        ->whereHas('departments', function($query) {
            $query->whereIn('departments.id', $this->all_manager_department_ids);
        })
        ->whereNotIn('users.id', [auth()->user()->id])

        ->first();


     if ($value == $authUser->id) {
            $this->customMessage = 'The :attribute is invalid. You cannot update data to yourself.';
            return false;
        }

        return $user?1:0;
    }

    public function message()
    {
       return $this->customMessage ?: 'The :attribute is invalid.';
    }



}
