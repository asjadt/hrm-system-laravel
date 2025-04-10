<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserAssetCreateRequest extends BaseFormRequest
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
                'nullable',
                'numeric',
                new ValidUserId($all_manager_department_ids),
            ],

            'name' => "required|string",
            "is_working" => "required|boolean",
            "status" => "required|string|in:available,assigned,returned,damaged,lost,reserved,repair_waiting",
            'code' => "required|string",
            'serial_number' => "required|string",
            'type' => "required|string",
            'image' => "nullable|string",
            'date' => "required|date",
            'note' => "required|string",
        ];
    }
    public function messages()
    {
        return [

            'status.in' => 'Invalid value for status. Valid values are: available, assigned, returned, damaged, lost, reserved, repair waiting.',


     
        ];
    }
}
