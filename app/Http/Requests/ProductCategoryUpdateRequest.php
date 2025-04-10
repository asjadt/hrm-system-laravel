<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryUpdateRequest extends BaseFormRequest
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
            "id" => "required|numeric|unique:product_categories,name," . $this->id . ",id",
            "name" => "required|string",
            "icon"=> "nullable|string",
            "description" => "nullable|string",
            "image" => "nullable|string",

        ];
    }
}
