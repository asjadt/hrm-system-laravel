<?php


namespace App\Http\Requests;

use App\Http\Utils\ErrorUtil;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    use ErrorUtil;
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $this->storeError($errors, 422, "line in request folder", "file in request folder");

        parent::failedValidation($validator);
    }


}
