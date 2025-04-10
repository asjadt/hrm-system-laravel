<?php

namespace App\Http\Requests;

use App\Models\AssetType;
use Illuminate\Foundation\Http\FormRequest;

class AssetTypeUpdateRequest extends BaseFormRequest
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

                    $asset_type_query_params = [
                        "id" => $this->id,
                    ];
                    $asset_type = AssetType::where($asset_type_query_params)
                        ->first();
                    if (!$asset_type) {

                            $fail("no asset type found");
                            return 0;

                    }

                        if(($asset_type->business_id != auth()->user()->business_id)) {

                            $fail("You do not have permission to update this designation due to role restrictions.");
                            return 0;
                        }





                },
            ],


            'name' => 'required|string',
            'description' => 'nullable|string',
        ];

        if (!empty(auth()->user()->business_id)) {
            $rules['name'] .= '|unique:asset_types,name,'.$this->id.',id,business_id,' . auth()->user()->business_id;
        } else {
        
        }

return $rules;
    }


}
