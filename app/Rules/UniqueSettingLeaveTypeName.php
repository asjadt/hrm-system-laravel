<?php

namespace App\Rules;

use App\Models\SettingLeaveType;
use Illuminate\Contracts\Validation\Rule;

class UniqueSettingLeaveTypeName implements Rule
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

        $exists = SettingLeaveType::where("setting_leave_types.name",$value)
        ->when(!empty($this->id), function($query) {
            $query->whereNotIn('id', [$this->id]);
        })
        ->when(empty(auth()->user()->business_id), function ($query)  {

             $query->where(function($query) {
                if (auth()->user()->hasRole('superadmin')) {
                    return $query->where('setting_leave_types.business_id', NULL)
                        ->where('setting_leave_types.is_default', 1)
                        ->where('setting_leave_types.is_active', 1);

                } else {
                    return $query->where('setting_leave_types.business_id', NULL)
                        ->where('setting_leave_types.is_default', 1)
                        ->where('setting_leave_types.is_active', 1)
                        ->whereDoesntHave("disabled", function($q) {
                            $q->whereIn("disabled_setting_leave_types.created_by", [auth()->user()->id]);
                        })

                        ->orWhere(function ($query)   {
                            $query->where('setting_leave_types.business_id', NULL)
                                ->where('setting_leave_types.is_default', 0)
                                ->where('setting_leave_types.created_by', auth()->user()->id)
                                ->where('setting_leave_types.is_active', 1);


                        });
                }
            });

        })
            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by) {
                return $query

                ->where(function ($query) use($created_by) {
                    $query->where('setting_leave_types.business_id', auth()->user()->business_id)
                        ->where('setting_leave_types.is_default', 0)
                        ->whereDoesntHave("disabled", function ($q) use ($created_by) {
                            $q->whereIn("disabled_setting_leave_types.created_by", [$created_by]);
                        })
                        ->whereDoesntHave("disabled", function ($q) use ($created_by) {
                            $q->whereIn("disabled_setting_leave_types.business_id", [auth()->user()->business_id]);
                        })
                        ;
                });
            })
        ->exists();
     return   !$exists;

    }

    public function message()
    {
        return 'name is already exist.';
    }
}
