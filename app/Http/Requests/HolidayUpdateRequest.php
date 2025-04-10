<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\User;
use App\Rules\ValidateDepartment;
use App\Rules\ValidateHolidayDate;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class HolidayUpdateRequest extends BaseFormRequest
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
        $all_user_of_manager = $this->get_all_user_of_manager($all_manager_department_ids);

        return [
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use($all_manager_department_ids, $all_user_of_manager){
                    $exists = Holiday::where('id', $value)
                    ->where(function ($query) use ($all_manager_department_ids, $all_user_of_manager) {
                        $query->whereHas("departments", function ($query) use ($all_manager_department_ids) {
                            $query->whereIn("departments.id", $all_manager_department_ids);
                        })
                            ->orWhereHas("users", function ($query) use ($all_user_of_manager) {
                                $query->whereIn(
                                    "users.id",
                                    $all_user_of_manager
                                );
                            })
                            ->when(auth()->user()->hasRole("business_owner"), function($query) {
                                $query->orWhere(function ($query) {
                                    $query->whereDoesntHave("users")
                                        ->whereDoesntHave("departments");
                                });
                            });

                    })

                    ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                        return;
                    }
                },
            ],




             'name' => 'required|string',
            'description' => 'nullable|string',

            'start_date' => [
                'required',
                'date',
                new ValidateHolidayDate($this->id)
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                new ValidateHolidayDate($this->id)
            ],

            'repeats_annually' => 'required|boolean',
            'departments' => 'present|array',
            'departments.*' => [
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ],
            'users' => 'present|array',
            'users.*' => [
                "numeric",
                new ValidUserId($all_manager_department_ids)

            ],
        ];
    }
}
