<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessUpdatePensionRequest extends FormRequest
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
            'business.id' => 'required|numeric|required|exists:businesses,id',
            'business.pension_scheme_registered' => 'required|boolean',
            'business.pension_scheme_name' => 'nullable|required_if:business.pension_scheme_registered,1|string',

            'business.pension_scheme_letters' => 'present|array',
            'business.pension_scheme_letters.*' => 'string',



        ];
    }
}
