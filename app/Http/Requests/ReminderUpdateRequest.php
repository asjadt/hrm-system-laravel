<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ReminderUpdateRequest extends BaseFormRequest
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
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('reminders')
                        ->where('id', $value)
                        ->where('reminders.business_id', '=', auth()->user()->business_id)
                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],
            'title' => 'required|string',
            'duration' => 'required|numeric',
            'duration_unit' => 'required|in:days,weeks,months',
            'send_time' => 'required|in:before_expiry,after_expiry',
            'frequency_after_first_reminder' => 'required|integer',
            'reminder_limit' => "nullable|integer",
            'keep_sending_until_update' => 'required|boolean',
            'entity_name' => 'required|string',

        ];
    }
    public function messages()
    {
        return [
            'duration_unit.in' => 'The :attribute valid values are days, weeks, months.',
            'send_time.in' => 'The :attribute valid values are before_expiry, after_expiry.'
        ];
    }
}
