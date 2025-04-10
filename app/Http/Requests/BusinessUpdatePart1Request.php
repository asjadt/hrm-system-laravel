<?php

namespace App\Http\Requests;

use App\Rules\SomeTimes;
use Illuminate\Foundation\Http\FormRequest;

class BusinessUpdatePart1Request extends BaseFormRequest
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
            'user.id' => 'required|numeric|exists:users,id',
            'user.first_Name' => 'required|string|max:255',
            'user.middle_Name' => 'nullable|string|max:255',

            'user.last_Name' => 'required|string|max:255',

            'user.email' => 'required|string|email|unique:users,email,' . $this->user["id"] . ',id',

            'user.password' => 'nullable|string|min:6',
            'user.phone' => 'nullable|string',
            'user.image' => 'nullable|string',
            'user.gender' => 'nullable|string|in:male,female,other',


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








    ];
}

}
