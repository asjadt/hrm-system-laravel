<?php

namespace App\Rules;

use App\Models\EmploymentStatus;
use Illuminate\Contracts\Validation\Rule;

class ValidEmploymentStatusName implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

     protected $id;
    public function __construct($id)
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
        $created_by  = NULL;
        if(auth()->user()->business) {
            $created_by = auth()->user()->business->created_by;
        }

        $exists = EmploymentStatus::where("employment_statuses.name",$value)
        ->when(!empty($this->id),function($query) {
            $query->whereNotIn("id",[$this->id]);
        })

        ->when(empty(auth()->user()->business_id), function ($query) {


            $query->where(function($query) {
                if (auth()->user()->hasRole('superadmin')) {
                    return $query->where('employment_statuses.business_id', NULL)
                        ->where('employment_statuses.is_default', 1)
                        ->where('employment_statuses.is_active', 1);

                } else {
                    return $query->where('employment_statuses.business_id', NULL)
                        ->where('employment_statuses.is_default', 1)
                        ->where('employment_statuses.is_active', 1)
                        ->whereDoesntHave("disabled", function($q) {
                            $q->whereIn("disabled_employment_statuses.created_by", [auth()->user()->id]);
                        })

                        ->orWhere(function ($query)   {
                            $query->where('employment_statuses.business_id', NULL)
                                ->where('employment_statuses.is_default', 0)
                                ->where('employment_statuses.created_by', auth()->user()->id)
                                ->where('employment_statuses.is_active', 1);


                        });
                }
            });



        })
            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by, $value) {

                $query->where(function($query)  use($created_by){
                    $query->where('employment_statuses.business_id', NULL)
                    ->where('employment_statuses.is_default', 1)
                    ->where('employment_statuses.is_active', 1)
                    ->whereDoesntHave("disabled", function($q) use($created_by) {
                        $q->whereIn("disabled_employment_statuses.created_by", [$created_by]);
                    })
                    ->whereDoesntHave("disabled", function($q)  {
                        $q->whereIn("disabled_employment_statuses.business_id",[auth()->user()->business_id]);
                    })

                    ->orWhere(function ($query) use( $created_by){
                        $query->where('employment_statuses.business_id', NULL)
                            ->where('employment_statuses.is_default', 0)
                            ->where('employment_statuses.created_by', $created_by)
                            ->where('employment_statuses.is_active', 1)
                            ->whereDoesntHave("disabled", function($q) {
                                $q->whereIn("disabled_employment_statuses.business_id",[auth()->user()->business_id]);
                            });
                    })
                    ->orWhere(function ($query)  {
                        $query->where('employment_statuses.business_id', auth()->user()->business_id)
                            ->where('employment_statuses.is_default', 0)
                            ->where('employment_statuses.is_active', 1);

                    });
                });

            })
        ->exists();
        return !$exists;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is already taken.';
    }
}
