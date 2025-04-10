<?php

namespace App\Http\Requests;

use App\Models\Designation;
use App\Rules\ValidateDesignationName;
use Illuminate\Foundation\Http\FormRequest;

class DesignationUpdateRequest extends BaseFormRequest
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



        $rules = [

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $designation_query_params = [
                        "id" => $this->id,
                    ];
                    $designation = Designation::where($designation_query_params)
                        ->first();
                    if (!$designation) {

                            $fail("no designation found");
                            return 0;

                    }
                    if (empty(auth()->user()->business_id)) {

                        if(auth()->user()->hasRole('superadmin')) {
                            if(($designation->business_id != NULL || $designation->is_default != 1)) {

                                $fail("You do not have permission to update this designation due to role restrictions.");

                          }

                        } else {
                            if(($designation->business_id != NULL || $designation->is_default != 0 || $designation->created_by != auth()->user()->id)) {

                                $fail("You do not have permission to update this designation due to role restrictions.");

                          }
                        }

                    } else {
                        if(($designation->business_id != auth()->user()->business_id || $designation->is_default != 0)) {

                            $fail("You do not have permission to update this designation due to role restrictions.");
                        }
                    }




                },
            ],


            'name' => 'required|string',
            'description' => 'nullable|string',
            'name' => [
                "required",
                'string',
                new   ValidateDesignationName($this->id)
            ],
        ];

    

return $rules;
    }
}
