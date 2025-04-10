<?php

namespace App\Http\Requests;

use App\Rules\ValidateHolidayDate;
use Illuminate\Foundation\Http\FormRequest;

class HolidaySelfCreateRequest extends FormRequest
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
            'name' => 'required|string',
            'description' => 'nullable|string',

            'start_date' => [
                'required',
                'date',
                new ValidateHolidayDate()
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                new ValidateHolidayDate()
            ],


            'repeats_annually' => 'required|boolean',




        ];
    }
}
