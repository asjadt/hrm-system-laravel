<?php

namespace App\Http\Controllers;

use App\Mail\UserPaymentFailed;
use App\Mail\UserRegistered;
use App\Models\Business;
use App\Models\ServicePlan;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\WebhookEndpoint;

class SubscriptionController extends Controller
{
    public function redirectUserToStripe(Request $request)
    {
        $id = $request->id;


        if (strlen($id) >= 20) {

            $trimmed_id = substr($id, 10, -10);

        } else {
            throw new Exception("invalid id");
        }
        $business = Business::findOrFail($trimmed_id);
        $user = User::findOrFail($business->owner_id);
        Auth::login($user);

        $systemSetting = SystemSetting::first();

        if (empty($systemSetting)) {
            return response()->json([
                "message" => "self registration is not supported"
            ], 403);
        }
        if (empty($systemSetting->self_registration_enabled)) {
            return response()->json([
                "message" => "self registration is not supported"
            ], 403);
        }

        Stripe::setApiKey($systemSetting->STRIPE_SECRET);
        Stripe::setClientId($systemSetting->STRIPE_KEY);


        $webhookEndpoints = WebhookEndpoint::all();


        $existingEndpoint = collect($webhookEndpoints->data)->first(function ($endpoint) {
            return $endpoint->url === route('stripe.webhook');
        });
        if (!$existingEndpoint) {

            $webhookEndpoint = WebhookEndpoint::create([
                'url' => route('stripe.webhook'),
                'enabled_events' => [
                    'checkout.session.completed',
                    'invoice.payment_succeeded',
                ],
            ]);
        }





        $service_plan = ServicePlan::where([
            "id" => $business->service_plan_id
        ])
            ->first();


        if (!$service_plan) {
            return response()->json([
                "message" => "no service plan found"
            ], 404);
        }


        if (empty($user->stripe_id)) {
            $stripe_customer = \Stripe\Customer::create([
                'email' => $user->email,
            ]);

            $user->stripe_id = $stripe_customer->id;
            $user->save();
        }



        $session_data = [
            'payment_method_types' => ['card'],
            'metadata' => [
                'our_url' => route('stripe.webhook'),
                'service_plan_id' => $service_plan->id,
                'service_plan_name' => $service_plan->name,

            ],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'GBP',
                        'product_data' => [
                            'name' => 'Your Service set up amount',
                        ],
                        'unit_amount' => $service_plan->set_up_amount * 100,
                    ],
                    'quantity' => 1,
                ],
                [
                    'price_data' => [
                        'currency' => 'GBP',
                        'product_data' => [
                            'name' => 'Your Service monthly amount',
                        ],
                        'unit_amount' => $service_plan->price * 100,
                        'recurring' => [
                            'interval' => "month",
                            'interval_count' => $service_plan->duration_months,
                        ],
                    ],
                    'quantity' => 1,
                ]

            ],
            'subscription_data' => [
                'metadata' => [
                    'our_url' => route('stripe.webhook'),
                    'service_plan_id' => $service_plan->id,
                    'service_plan_name' => $service_plan->name,
                ],
            ],
            'customer' => $user->stripe_id  ?? null,

            'mode' => 'subscription',
            'success_url' => route('subscription.success_payment', ['user_id' => base64_encode($user->id)]),
            'cancel_url' => route('subscription.failed_payment', ['user_id' => base64_encode($user->id)]),
        ];





        if (!empty($business->service_plan_discount_amount) && $business->service_plan_discount_amount > 0) {



            $coupon = \Stripe\Coupon::create([
                'amount_off' => $business->service_plan_discount_amount * 100,
                'currency' => 'GBP',
                'duration' => 'once',
                'name' => $business->service_plan_discount_code,
            ]);

            $session_data["discounts"] =  [
                [
                    'coupon' => $coupon,
                ],
            ];
        }

        $session = Session::create($session_data);

        return redirect()->to($session->url);
    }



    public function stripePaymentSuccess(Request $request)
    {


        return redirect()->to(env("FRONT_END_URL") . "/verify/business?status=success");
    }


    public function stripePaymentFailed(Request $request)
    {
        $user_id = base64_decode($request->query('user_id'));


        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $reseller = $user->business->reseller;

        if (env("SEND_EMAIL") == true) {
            try {
                Mail::to(['kids20acc@gmail.com', 'ralashwad@gmail.com', $reseller->email])->send(new UserPaymentFailed($user));
            } catch (\Exception $e) {
               
                Log::error("Failed to send email: " . $e->getMessage(), ['exception' => $e]);
            }
        }

        return redirect()->to(env("FRONT_END_URL") . "/verify/business?status=failed");
    }


}
