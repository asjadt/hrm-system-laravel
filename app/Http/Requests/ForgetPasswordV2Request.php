<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgetPasswordV2Request extends BaseFormRequest
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
            'id' => 'required|numeric',
            'client_site' => 'string|required|in:client,dashboard',
        ];
    }
    public function messages()
    {

        return [
            'client_site.in' => 'The client_site field must be either "client" or "dashboard".',
        ];
    }
}
