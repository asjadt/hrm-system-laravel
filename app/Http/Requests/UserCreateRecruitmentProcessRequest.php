<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\RecruitmentProcess;
use App\Rules\ValidateRecruitmentProcessId;
use App\Rules\ValidateRecruitmentProcessName;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserCreateRecruitmentProcessRequest extends FormRequest
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
                "required",
                "numeric",
                new ValidUserId($all_manager_department_ids),
            ],
            'recruitment_processes' => "present|array",

            'recruitment_processes.*.recruitment_process_id' => [
                "required",
                'numeric',
                new ValidateRecruitmentProcessId()

            ],
            'recruitment_processes.*.description' => "nullable|string",
            'recruitment_processes.*.attachments' => "present|array",
        ];
    }
}
