<?php

namespace App\Http\Requests;

use App\Rules\ValidateDiscountCode;
use Illuminate\Foundation\Http\FormRequest;

class ServicePlanCreateRequest extends BaseFormRequest
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
            "name" => "required|string",
            "description" => "nullable|string",
            'set_up_amount' => 'required|numeric',
            'duration_months' => 'required|numeric',
            'price' => 'required|numeric',

            'business_tier_id' => 'required|exists:business_tiers,id',

            "discount_codes" => "present|array",
            "discount_codes.*.code" => [
"required",
new ValidateDiscountCode()
            ],




            "discount_codes.*.discount_amount" => "required|numeric",

        ];
    }
}
