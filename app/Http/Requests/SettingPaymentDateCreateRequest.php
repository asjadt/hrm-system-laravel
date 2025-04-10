<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingPaymentDateCreateRequest extends BaseFormRequest
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
            'payment_type' => 'required|in:weekly,monthly,custom',
            'custom_date' => 'nullable|required_if:payment_type,custom|date',
            'day_of_week' => 'nullable|required_if:payment_type,weekly|integer|min:0|max:6',
            'day_of_month' => 'nullable|required_if:payment_type,monthly|integer|min:1|max:31',
            'custom_frequency_interval' => 'required_if:payment_type,custom|integer|min:1',
            'custom_frequency_unit' => 'required_if:payment_type,custom|in:days,weeks,months',
            'role_specific_settings' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'payment_type.in' => 'The :attribute field must be one of the following: weekly, monthly, custom.',
            'custom_frequency_unit.in' => 'The :attribute field must be one of the following: days, weeks, months.',

        ];
    }
}
