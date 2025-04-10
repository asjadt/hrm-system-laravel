<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => "required|numeric",
            'roles' => "present|array",
            'roles.*' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {
                    $role  = Role::where(["name" => $value])->first();

                    if (!$role){

                             $fail("Role does not exists.");
                             return;

                    }

                    if(!empty(auth()->user()->business_id)) {
                        if (empty($role->business_id)){

                          $fail("You don't have this role");
                          return;

                      }
                        if ($role->business_id != auth()->user()->business_id){

                            $fail("You don't have this role");
                            return;

                        }
                    } else {
                        if (!empty($role->business_id)){
                    
                          $fail("You don't have this role");
                          return;

                      }
                    }



                },
            ],
        ];
    }
}
