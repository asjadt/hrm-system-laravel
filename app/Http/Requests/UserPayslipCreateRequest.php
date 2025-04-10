<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\User;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserPayslipCreateRequest extends BaseFormRequest
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


            "payroll_id" => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use($all_manager_department_ids) {


                  $exists =  Payroll::where(
                    [
                        "payrolls.user_id" => $this->user_id,

                    ])
                    ->whereHas("user.department_user.department", function($query) use($all_manager_department_ids)  {
                        $query->whereIn("departments.id",$all_manager_department_ids);
                     })
                     ->first();

            if (!$exists) {
                $fail($attribute . " is invalid.");
                return;
            }


                },
            ],


            'month' => 'required|integer',
            'year' => 'required|integer',
            'payment_amount' => 'required|numeric',
            'payment_notes' => 'nullable|string',
            'payment_date' => 'required|date',
            'payslip_file' => 'nullable|string',

            'payment_record_file' => 'present|array',
            'payment_record_file.*' => 'string',

            'gross_pay' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'employee_ni_deduction' => 'required|numeric|min:0',
            'employer_ni' => 'required|numeric|min:0',
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash,cheque,other'],


        ];



    }
    public function messages()
    {
        return [
            'payment_method.required' => 'The payment method field is required.',
            'payment_method.in' => 'Invalid payment method selected. Valid options are: bank_transfer, cash, cheque, or other.',
        ];
    }
}
