<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Models\UserAsset;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserAssetAddExistingRequest extends BaseFormRequest
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
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($all_manager_department_ids) {
                    $exists = UserAsset::where('id', $value)
                        ->where('user_assets.business_id', '=', auth()->user()->business_id)
                        ->where(function($query) use($all_manager_department_ids) {
                            $query->whereHas("user.department_user.department", function($query) use($all_manager_department_ids) {
                                $query->whereIn("departments.id",$all_manager_department_ids);
                             })
                             ->orWhere('user_assets.user_id', NULL)
                             ;

                          })

                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],

        ];
    }
}
