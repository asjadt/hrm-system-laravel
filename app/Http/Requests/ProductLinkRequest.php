<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductLinkRequest extends BaseFormRequest
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

            "type" => "required|in:single,variable",
            "name" => "required|string",
            "description" => "nullable|string",
            "product_category_id" => "required|numeric",
            "shop_id"  =>  "required|numeric" ,
            "image"  => "nullable|string",
            "images" =>"nullable|array",
            "images.*"  => "string",



        "price" => "required_if:type,single",
        "quantity" => "required_if:type,single",




        "product_variations" => "required_if:type,variable|array",
        "product_variations.*.price"  => "required_if:type,variable|not_in:0,0",
        "product_variations.*.quantity"  => "required_if:type,variable|numeric",

   ];
    }
}
