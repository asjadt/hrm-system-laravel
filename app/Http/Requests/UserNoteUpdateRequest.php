<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Models\UserNote;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserNoteUpdateRequest extends BaseFormRequest
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

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = UserNote::where('id', $value)
                        ->where('user_notes.user_id', '=', $this->user_id)
                        ->when( !auth()->user()->hasPermissionTo('business_owner'), function($query) {
                            $query->where('user_notes.created_by', '=', auth()->user()->id);
                        })

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
            'title' => 'required|string',
            'description' => 'required|string',
      

            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ];
    }
}
