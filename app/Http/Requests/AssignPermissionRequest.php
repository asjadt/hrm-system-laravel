<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class AssignPermissionRequest extends BaseFormRequest
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
            'permissions' => "present|array",
            'permissions.*' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {
                    $permission  = Permission::where(["name" => $value])->first();

                    if (!$permission){
                          
                             $fail("permission does not exists.");
                             return;

                    }




                },
            ],
        ];
    }
}
