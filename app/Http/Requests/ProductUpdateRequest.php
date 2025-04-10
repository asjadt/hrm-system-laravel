<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ProductUpdateRequest extends BaseFormRequest
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
        $shopIdRequired = (!$this->user()->hasRole("superadmin") && !$this->user()->hasRole("data_collector"));
             return [
                "id" => "required|numeric",

                "name" => "required|string",
                "description" => "nullable|string",
                "product_category_id" => "required|numeric",
                "shop_id"  => $shopIdRequired ? "required|numeric" : "nullable|numeric",
                "image"  => "nullable|string",
                "images" =>"nullable|array",
                "images.*"  => "nullable|string",

                'sku' => 'nullable|string|unique:products,sku,' . $this->id . ',id',

            "price" => "required_if:type,single",
            "quantity" => "required_if:type,single",




            "product_variations" => "required_if:type,variable|array",
            "product_variations.*.id"  => "nullable|numeric",
            "product_variations.*.price"  => "required_if:type,variable|not_in:0,0",
            "product_variations.*.quantity"  => "required_if:type,variable|numeric",
        ];
    }
}
