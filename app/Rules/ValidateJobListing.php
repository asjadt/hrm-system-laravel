<?php

namespace App\Rules;

use App\Models\JobListing;
use Illuminate\Contracts\Validation\Rule;

class ValidateJobListing implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
                         $exists = JobListing::where('id', $value)
                        ->where('job_listings.business_id', '=', auth()->user()->business_id)
                        ->exists();

return $exists;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid.';
    }
}
