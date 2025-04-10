<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserRightToWorkHistoryCreateRequest extends BaseFormRequest
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

            'user_id' => [
                'required',
                'numeric',
                new ValidUserId($all_manager_department_ids)
            ],
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',


            'right_to_work_code' => 'required|string',
            'right_to_work_check_date' => 'required|date',
            'right_to_work_expiry_date' => 'required|date',

            'right_to_work_docs' => 'required|array',
            'right_to_work_docs.*.file_name' => 'required|string',
            'right_to_work_docs.*.description' => 'required|string',


        ];
    }
}
