<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class MultipleFileUploadRequestV2 extends FormRequest
{
    use BasicUtil;
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

        $all_manager_department_ids = $this->get_all_departments_of_manager();


        return [
            'files' => 'required|array',
            'files.*' => 'required|file|max:5120',
            "user_id" => [
                "nullable",
                "numeric",
                new ValidUserId($all_manager_department_ids)
            ],
            "folder_location" => "required|string",
            "is_public" => "required|boolean",
        ];
    }
}
