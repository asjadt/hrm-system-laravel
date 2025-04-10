<?php

namespace App\Http\Requests;

use App\Rules\SomeTimes;
use Illuminate\Foundation\Http\FormRequest;

class BusinessUpdatePart3Request extends BaseFormRequest
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

            "times" => "present|array",
            "times.*.day" => 'required|numeric',
            "times.*.is_weekend" => ['required',"boolean"],

            'times.*.start_at' => [
               'required',
               'date_format:H:i:s'
           ],
           'times.*.end_at' => [
               'required',
               'date_format:H:i:s'
           ],

        ];




        return $rules;



    }

    public function messages()
{
    return [
        'user.id.required' => 'The user ID field is required.',
        'user.id.numeric' => 'The user ID must be a numeric value.',
        'user.id.exists' => 'The selected user ID is invalid.',

        'user.first_Name.required' => 'The first name field is required.',
        'user.first_Name.string' => 'The first name field must be a string.',
        'user.first_Name.max' => 'The first name field may not be greater than :max characters.',

        'user.last_Name.required' => 'The last name field is required.',
        'user.last_Name.string' => 'The last name field must be a string.',
        'user.last_Name.max' => 'The last name field may not be greater than :max characters.',

        'user.email.required' => 'The email field is required.',
        'user.email.email' => 'The email must be a valid email address.',
        'user.email.string' => 'The email field must be a string.',
        'user.email.unique' => 'The email has already been taken.',
        'user.email.exists' => 'The selected email is invalid.',

        'user.password.confirmed' => 'The password confirmation does not match.',
        'user.password.string' => 'The password field must be a string.',
        'user.password.min' => 'The password must be at least :min characters.',


        'user.phone.string' => 'The phone field must be a string.',

        'user.image.nullable' => 'The image field must be nullable.',
        'user.gender.in' => 'The gender field must be in "male","female","other".',

        'business.id.required' => 'The business ID field is required.',
        'business.id.numeric' => 'The business ID must be a numeric value.',
        'business.id.exists' => 'The selected business ID is invalid.',

        'business.name.required' => 'The name field is required.',
        'business.name.string' => 'The name field must be a string.',
        'business.name.max' => 'The name field may not be greater than :max characters.',

        'business.about.string' => 'The about field must be a string.',
        'business.web_page.string' => 'The web page field must be a string.',
        'business.phone.string' => 'The phone field must be a string.',
     
        'business.email.email' => 'The email must be a valid email address.',
        'business.email.string' => 'The email field must be a string.',
        'business.email.unique' => 'The email has already been taken.',
        'business.email.exists' => 'The selected email is invalid.',
        'business.additional_information.string' => 'The additional information field must be a string.',

        'business.lat.required' => 'The latitude field is required.',
        'business.lat.string' => 'The latitude field must be a string.',


        'business.long.required' => 'The longitude field is required.',
        'business.long.string' => 'The longitude field must be a string.',

        'business.country.required' => 'The country field is required.',
        'business.country.string' => 'The country field must be a string.',

        'business.city.required' => 'The city field is required.',
        'business.city.string' => 'The city field must be a string.',

        'business.currency.required' => 'The currency field is required.',
        'business.currency.string' => 'The currency must be a string.',

        'business.postcode.string' => 'The postcode field must be a string.',

        'business.address_line_1.required' => 'The address line 1 field is required.',
        'business.address_line_1.string' => 'The address line 1 field must be a string.',

        'business.address_line_2.string' => 'The address line 2 field must be a string.',

        'business.logo.string' => 'The logo field must be a string.',
        'business.image.string' => 'The image field must be a string.',

        'business.images.array' => 'The images field must be an array.',
        'business.images.*.string' => 'Each image in the images field must be a string.',






    ];
}

}

