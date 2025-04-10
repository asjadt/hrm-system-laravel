<?php

use App\Http\Controllers\CustomWebhookController;

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DeveloperLoginController;


use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/send-test-mail', function () {
    $user = (object) [
        'email' => 'rifatbilalphilips@gmail.com',
        'name' => 'Test User'
    ];


    Mail::send([], [], function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Test Mail')
            ->setBody("
                    <h1>Hello, {$user->name}</h1>

                    <p>This is a test email sent to rifatbilalphilips@gmail.com</p>
                ", 'text/html');
    });

    return "Test email sent!";
});

Route::get("/", function() {
    return view("welcome");
});

Route::get("/subscriptions/redirect-to-stripe",[SubscriptionController::class,"redirectUserToStripe"]);
Route::get("/subscriptions/get-success-payment",[SubscriptionController::class,"stripePaymentSuccess"])->name("subscription.success_payment");
Route::get("/subscriptions/get-failed-payment",[SubscriptionController::class,"stripePaymentFailed"])->name("subscription.failed_payment");




Route::get("/activate/{token}",function(Request $request,$token) {
    $user = User::where([
        "email_verify_token" => $token,
    ])
        ->where("email_verify_token_expires", ">", now())
        ->first();
    if (!$user) {
        return response()->json([
            "message" => "Invalid Url Or Url Expired"
        ], 400);
    }

    $user->email_verified_at = now();
    $user->save();


    $email_content = EmailTemplate::where([
        "type" => "welcome_message",
        "is_active" => 1

    ])->first();


    $html_content = json_decode($email_content->template);
    $html_content =  str_replace("[FirstName]", $user->first_Name, $html_content );
    $html_content =  str_replace("[LastName]", $user->last_Name, $html_content );
    $html_content =  str_replace("[FullName]", ($user->first_Name. " " .$user->last_Name), $html_content );
    $html_content =  str_replace("[AccountVerificationLink]", (env('APP_URL').'/activate/'.$user->email_verify_token), $html_content);
    $html_content =  str_replace("[ForgotPasswordLink]", (env('FRONT_END_URL').'/fotget-password/'.$user->resetPasswordToken), $html_content );



    $email_template_wrapper = EmailTemplateWrapper::where([
        "id" => $email_content->wrapper_id
    ])
    ->first();


    $html_final = json_decode($email_template_wrapper->template);
    $html_final =  str_replace("[content]", $html_content, $html_final);


    return view("dynamic-welcome-message",["html_content" => $html_final]);
});

