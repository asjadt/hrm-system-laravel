<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\SocialSite;
use App\Models\User;
use App\Models\UserSocialSite;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserSocialSiteUpdateRequest extends BaseFormRequest
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
      return  [



            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = UserSocialSite::where('id', $value)
                        ->where('user_social_sites.user_id', '=', $this->user_id)
                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],

            'social_site_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = SocialSite::where('id', $value)
                        ->where('social_sites.is_active', 1)
                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],
            'user_id' => [
                'required',
                'numeric',
                new ValidUserId($all_manager_department_ids)
            ],


            'profile_link' => "nullable|string",

        ];
    }
}
