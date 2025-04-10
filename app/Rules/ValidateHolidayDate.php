<?php

namespace App\Rules;

use App\Models\Holiday;
use Illuminate\Contracts\Validation\Rule;

use function PHPUnit\Framework\isEmpty;

class ValidateHolidayDate implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $id;
    public function __construct($id = NULL)
    {
        $this->id = $id;
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

        return 1;
        $existingHolidays = Holiday::where(
            [
                "holidays.business_id" => auth()->user()->business_id
            ]
            )

            ->where('holidays.start_date', "<=", $value)
            ->where('holidays.end_date', ">=", $value)
            ->when(!empty($this->id), function($query) {
                 $query->whereNotIn("holidays.id",[$this->id]);
            })
            ->get();

            return $existingHolidays->isEmpty()?1:0;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid. A Holiday exists on this day.';
    }
}
