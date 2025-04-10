<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\UserRegistered;
use App\Mail\UserSubscriptionRenewed;
use App\Models\ServicePlan;
use App\Models\BusinessSubscription;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Stripe\Event;

class CustomWebhookController extends WebhookController
{
    use UserActivityUtil, ErrorUtil;
    /**
     * Handle a Stripe webhook call.
     *
     * @param  Event  $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleStripeWebhook(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $payload = $request->all();


            Log::info('Webhook Payload: ' . json_encode($payload));


            $eventType = $payload['type'] ?? null;


            Log::info('Event Type: ' . $eventType);


        if ($eventType === 'checkout.session.completed') {
            $this->handleChargeSucceeded($payload['data']['object']);
        }

   if ($eventType === 'invoice.payment_succeeded') {
    $this->handleSubscriptionPaymentSucceeded($payload['data']['object']);

   }


        return response()->json(['message' => 'Webhook received']);
        } catch (Exception $e) {

            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     * Handle payment succeeded webhook from Stripe.
     *
     * @param  array  $paymentCharge
     * @return void
     */
    protected function handleChargeSucceeded($data)
    {



        $amount = isset($data['amount_total'])
            ? $data['amount_total'] / 100
            : null;

        $customerID = $data['customer'] ?? null;
        $metadata = $data["metadata"] ?? [];


        if (!empty($metadata["our_url"]) && $metadata["our_url"] != route('stripe.webhook')) {
            return;
        }

        $user = User::where("stripe_id", $customerID)->first();


        if (!empty($metadata["service_plan_id"])) {
            $service_plan = ServicePlan::find($metadata["service_plan_id"]);
        } else {
            $service_plan = ServicePlan::find($user->business->service_plan_id);
        }

        $subscription = BusinessSubscription::create([
            'business_id' => $user->business->id,
            'service_plan_id' => $service_plan->id,
            'start_date' => "1970-01-01 00:00:00",
            'end_date' => "1970-01-01 00:00:00",
            'amount' => $amount,
            'paid_at' => now(),
            'transaction_id' => $data['id'],

        ]);

        $reseller = $user->business->reseller;

        if (env("SEND_EMAIL") == true) {
            try {
                Mail::to(['kids20acc@gmail.com', 'ralashwad@gmail.com', $reseller->email])->send(new UserRegistered($user, $subscription));
            } catch (Exception $e) {
                Log::error("Failed to send email: " . $e->getMessage(), ['exception' => $e]);
            }
        }



    }

    protected function handleSubscriptionPaymentSucceeded($invoice)
    {


        if (isset($invoice['subscription'])) {



            $lastIndex = count($invoice['lines']['data']) - 1;
            $amount = isset($invoice['lines']['data'][$lastIndex]['amount'])
                ? $invoice['lines']['data'][$lastIndex]['amount'] / 100
                : null;

            $customerID = $invoice['customer'] ?? null;
            $subscriptionID = $invoice['subscription'];
            $metadata = $invoice["subscription_details"]["metadata"] ?? [];
            $periodStart = isset($invoice['lines']['data'][0]['period']['start'])
                ? Carbon::createFromTimestamp($invoice['lines']['data'][0]['period']['start'])
                : null;

            $periodEnd = isset($invoice['lines']['data'][0]['period']['end'])
                ? Carbon::createFromTimestamp($invoice['lines']['data'][0]['period']['end'])
                : null;


            if (!empty($metadata["our_url"]) && $metadata["our_url"] != route('stripe.webhook')) {
                return;
            }

            $user = User::where("stripe_id", $customerID)->first();

            if (!$user) {

                Log::error("User not found for customer ID: $customerID");
                return response()->json([
                    "message" => "User not found for customer ID: $customerID"
                ], 400);
            }

            $service_plan = !empty($metadata["service_plan_id"])
                ? ServicePlan::find($metadata["service_plan_id"])
                : ServicePlan::find($user->business->service_plan_id);

            if (!$service_plan) {

                Log::error("Service plan not found for user ID: $user->id");
                return response()->json([
                    "message" => "Service plan not found for user ID: $user->id"
                ], 400);
            }

            $subscription = BusinessSubscription::create([
                'business_id' => $user->business->id,
                'service_plan_id' => $service_plan->id,
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'amount' => ($amount),
                'paid_at' => now(),
                'transaction_id' => $invoice['id'],
                'subscription_id' => $subscriptionID
            ]);


            $subscription_count = BusinessSubscription::where([
                'business_id' => $user->business->id
            ])
                ->count();

            if ($subscription_count > 2) {
                $reseller = $user->business->reseller;

                if (env("SEND_EMAIL") == true) {
                    try {
                        Mail::to(['kids20acc@gmail.com', 'ralashwad@gmail.com', $reseller->email])->send(new UserSubscriptionRenewed($user, $subscription));
                    } catch (Exception $e) {
                        Log::error("Failed to send email: " . $e->getMessage(), ['exception' => $e]);
                    }
                }



            }
        } else {
    
            Log::warning("Received non-subscription payment event.");
        }
    }


}
