<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessTimesUpdateRequest extends BaseFormRequest
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
             "times" => "required|array",
             "times.*.day" => 'required|numeric',
             "times.*.start_at" => 'required|date_format:H:i:s',
             "times.*.end_at" => 'required|date_format:H:i:s',
             "times.*.is_weekend" => ['required',"boolean"],
        ];
    }
}
