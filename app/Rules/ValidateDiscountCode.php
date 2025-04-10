<?php

namespace App\Rules;

use App\Models\ServicePlanDiscountCode;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class ValidateDiscountCode implements Rule
{

    private $id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */


    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $codes = collect(request()->input('discount_codes'))->pluck('code');
        $duplicates = $codes->duplicates()->all();
        if (in_array($value, $duplicates)) {
            return true;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid. Duplicate Entry';
    }
}
