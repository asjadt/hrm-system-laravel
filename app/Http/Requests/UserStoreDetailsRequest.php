<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreDetailsRequest extends BaseFormRequest
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
            'user_id' => 'required|numeric',
            'date_assigned' => 'required|date',
            'expiry_date' => 'required|date',
            'status' => 'required|in:pending,approved,denied,visa_granted',
            'note' => 'nullable|string',
        ];

        if ($this->input('status') === 'visa_granted') {
            $rules['passport_details.passport_number'] = 'required|string';
            $rules['passport_details.passport_issue_date'] = 'required|date';
            $rules['passport_details.passport_expiry_date'] = 'required|date';
            $rules['passport_details.place_of_issue'] = 'required|string';



            $rules['passport_details.visa_details.BRP_number'] = 'required|string';
            $rules['passport_details.visa_details.visa_issue_date'] = 'required|date';
            $rules['passport_details.visa_details.visa_expiry_date'] = 'required|date';
            $rules['passport_details.visa_details.place_of_issue'] = 'required|string';
            $rules['passport_details.visa_details.visa_docs'] = 'present|array';
            $rules['passport_details.visa_details.visa_docs.*.file_name'] = 'required|string';
            $rules['passport_details.visa_details.visa_docs.*.description'] = 'nullable|string';

        }

        return $rules;
    }

    public function messages()
    {
        return [

            'status.in' => 'Invalid value for status. Valid values are: pending,approved,denied,visa_granted.',
        
        ];
    }



}
