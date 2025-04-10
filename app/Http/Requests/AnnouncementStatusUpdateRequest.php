<?php

namespace App\Http\Requests;



class AnnouncementStatusUpdateRequest extends BaseFormRequest
{
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
        return [
            "announcement_ids" => "required|array",
            "announcement_ids.*" => "numeric|exists:announcements,id",
        ];
    }
}
