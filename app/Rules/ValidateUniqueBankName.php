<?php

namespace App\Rules;

use App\Models\Bank;
use Illuminate\Contracts\Validation\Rule;

class ValidateUniqueBankName implements Rule
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

        $exists = Bank::where("banks.name",$value)
        ->when(!empty($this->id),function($query) {
            $query->whereNotIn("id",[$this->id]);
        })

        ->when(empty(auth()->user()->business_id), function ($query)  {

            $query->where(function($query) {
                if (auth()->user()->hasRole('superadmin')) {
                    return $query->where('banks.business_id', NULL)
                        ->where('banks.is_default', 1)
                        ->where('banks.is_active', 1);

                } else {
                    return $query->where('banks.business_id', NULL)
                        ->where('banks.is_default', 1)
                        ->where('banks.is_active', 1)
                        ->whereDoesntHave("disabled", function($q) {
                            $q->whereIn("disabled_banks.created_by", [auth()->user()->id]);
                        })

                        ->orWhere(function ($query)   {
                            $query->where('banks.business_id', NULL)
                                ->where('banks.is_default', 0)
                                ->where('banks.created_by', auth()->user()->id)
                                ->where('banks.is_active', 1);


                        });
                }
            });

        })
            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by) {



            $query->where(function($query) use($created_by) {
                $query->where('banks.business_id', NULL)
                ->where('banks.is_default', 1)
                ->where('banks.is_active', 1)
                ->whereDoesntHave("disabled", function($q) use($created_by) {
                    $q->whereIn("disabled_banks.created_by", [$created_by]);
                })
                ->whereDoesntHave("disabled", function($q)  {
                    $q->whereIn("disabled_banks.business_id",[auth()->user()->business_id]);
                })

                ->orWhere(function ($query) use( $created_by){
                    $query->where('banks.business_id', NULL)
                        ->where('banks.is_default', 0)
                        ->where('banks.created_by', $created_by)
                        ->where('banks.is_active', 1)
                        ->whereDoesntHave("disabled", function($q) {
                            $q->whereIn("disabled_banks.business_id",[auth()->user()->business_id]);
                        });
                })
                ->orWhere(function ($query)   {
                    $query->where('banks.business_id', auth()->user()->business_id)
                        ->where('banks.is_default', 0)
                        ->where('banks.is_active', 1);

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
