<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingRequest extends FormRequest
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
            'self_registration_enabled'=> "required|boolean",
            'STRIPE_KEY' => "string|required_if:self_registration_enabled,1",
            "STRIPE_SECRET" => "string|required_if:self_registration_enabled,1"
        ];
    }
}
