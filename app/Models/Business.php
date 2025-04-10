<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Stripe\Stripe;

class Business extends Model
{
    use HasFactory;
    protected $appends = ['is_subscribed'];

    protected $fillable = [
        "name",
        "start_date",
        "trail_end_date",
        "about",
        "web_page",

        "phone",
        "email",
        "additional_information",
        "address_line_1",
        "address_line_2",
        "lat",
        "long",
        "country",
        "city",
        "currency",
        "postcode",
        "logo",
        "image",
        "background_image",
        "status",
        "is_active",
        "is_self_registered_businesses",

        "service_plan_id",
        "service_plan_discount_code",
        "service_plan_discount_amount",


        "pension_scheme_registered",
        "pension_scheme_name",
        "pension_scheme_letters",
        "number_of_employees_allowed",


        "owner_id",
        'created_by',
        "reseller_id"

    ];

    protected $casts = [
        'pension_scheme_letters' => 'array',
    ];

    protected $hidden = [
        'pin_code'
    ];



    public function reseller()
    {
        return $this->hasOne(User::class,"id","reseller_id");
    }

    private function isValidSubscription($subscription)
    {
        if (!$subscription) return false;


        if (empty($subscription->start_date) || empty($subscription->end_date)) return false;

        $startDate = Carbon::parse($subscription->start_date)->startOfDay();
        $endDate = Carbon::parse($subscription->end_date)->endOfDay();
        $today = Carbon::today();


    if ($startDate->isFuture()){
        return false;
    };


    if ($endDate->isPast() && !$endDate->isSameDay($today)){
        return false;
    };

     return true;
    }

    private function isTrailDateValid($trail_end_date)
    {

        if (empty($trail_end_date)) {
            return false;
        }


        $parsedDate = Carbon::parse($trail_end_date);
        return !( $parsedDate->isPast() && !$parsedDate->isToday() );
    }

    public function getIsSubscribedAttribute($value)
    {

        $user = auth()->user();
        if (empty($user)) {
            return 0;
        }



        if (!$this->is_active) {
            return 0;
        }


        if ($this->is_self_registered_businesses) {
            $validTrailDate = $this->isTrailDateValid($this->trail_end_date);
            $latest_subscription = BusinessSubscription::where('business_id', $this->id)
                ->where('service_plan_id', $this->service_plan_id)
                ->orderByDesc("business_subscriptions.id")
                ->first();

            if (!$this->isValidSubscription($latest_subscription) && !$validTrailDate) {
                return 0;
            }

        } else {

            if (!$this->isTrailDateValid($this->trail_end_date)) {
                return 0;
            }
        }

        return 1;
    }




    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }
    public function users()
    {
        return $this->hasMany(User::class, 'business_id', 'id');
    }

    public function service_plan()
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id', 'id');
    }

    public function subscription()
    {
        return $this->hasOne(BusinessSubscription::class, 'business_id', 'id')
        ->orderByDesc("business_subscriptions.id")
            ;
    }

    public function current_subscription()
    {
        return $this->hasOne(BusinessSubscription::class, 'business_id', 'id')
         ->where('business_subscriptions.service_plan_id', $this->service_plan_id)
         ->orderByDesc("business_subscriptions.id")
            ;
    }

    public function getStripeSubscriptionEnabledAttribute()
    {
        $systemSetting = SystemSetting::

            first();

        if (empty($systemSetting)) {
            return false;
        }
        if (empty($systemSetting->self_registration_enabled)) {
            return false;
        }

        Stripe::setApiKey($systemSetting->STRIPE_SECRET);
        Stripe::setClientId($systemSetting->STRIPE_KEY);


        if (!empty($this->owner->stripe_id)) {

    $subscriptions = \Stripe\Subscription::all([
        'customer' => $this->owner->stripe_id,
        'status' => 'active',
    ]);
            $subscriptions_not_ending = [];

    foreach ($subscriptions->data as $subscription) {
        if ($subscription->cancel_at_period_end === false) {

            $subscriptions_not_ending[] = $subscription;
        }
    }


    return count($subscriptions_not_ending) > 0;
        }

        return false;
    }


    public function default_work_shift()
    {
        return $this->hasOne(WorkShift::class, 'business_id', 'id')->where('is_business_default', 1);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }


    public function times()
    {
        return $this->hasMany(BusinessTime::class, 'business_id', 'id');
    }


    public function active_modules()
    {
        return $this->hasMany(BusinessModule::class, 'business_id', 'id');
    }







    protected static function boot()
    {
        parent::boot();


        static::deleting(function ($item) {

            $item->deleteFiles();
        });
    }





    public function deleteFiles()
    {

        $filePaths = $this->pension_scheme_letters;

    
        foreach ($filePaths as $filePath) {
            if (File::exists(public_path($filePath->file))) {
                File::delete(public_path($filePath->file));
            }
        }
    }
}
