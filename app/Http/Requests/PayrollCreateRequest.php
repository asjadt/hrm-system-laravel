<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\Payrun;
use App\Models\User;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class PayrollCreateRequest extends BaseFormRequest
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
            'payrun_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use($all_manager_department_ids) {
                    $exists = Payrun::where('id', $value)
                        ->where('payruns.business_id', '=', auth()->user()->business_id)
                        ->where(function($query) use($all_manager_department_ids) {
                            $query->whereHas("departments", function($query) use($all_manager_department_ids) {
                                $query->whereIn("departments.id",$all_manager_department_ids);
                             })
                             ->orWhereHas("users.department_user.department", function($query) use($all_manager_department_ids) {
                                $query->whereIn("departments.id",$all_manager_department_ids);
                             });
                        })
                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],
            'users' => 'present|array',
            'users.*' => [
                "numeric",
                new ValidUserId($all_manager_department_ids)

            ],
            "start_date" => "nullable|date",
            "end_date" => "nullable|date",


        ];
    }
}
