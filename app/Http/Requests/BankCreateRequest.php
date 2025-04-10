<?php

namespace App\Http\Requests;

use App\Models\Bank;
use App\Rules\ValidateUniqueBankName;
use Illuminate\Foundation\Http\FormRequest;

class BankCreateRequest extends BaseFormRequest
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

            'description' => 'nullable|string',
            'name' => [
                "required",
                'string',
              new  ValidateUniqueBankName(NULL),

            ],
        ];



return $rules;

    }
}
