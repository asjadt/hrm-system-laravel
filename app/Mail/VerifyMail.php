<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $email_content = EmailTemplate::where([
            "type" => "email_verification_mail",
            "is_active" => 1

        ])->first();


        $html_content = json_decode($email_content->template);
        $html_content =  str_replace("[FirstName]", $this->user->first_Name, $html_content );
        $html_content =  str_replace("[MiddleName]", $this->user->middle_Name, $html_content );

        $html_content =  str_replace("[LastName]", $this->user->last_Name, $html_content );
        $html_content =  str_replace("[FullName]", ($this->user->first_Name . " " . $this->user->middle_Name . " " .$this->user->last_Name), $html_content );
        $html_content =  str_replace("[AccountVerificationLink]", (env('APP_URL').'/activate/'.$this->user->email_verify_token), $html_content);
        $html_content =  str_replace("[ForgotPasswordLink]", (env('FRONT_END_URL').'/fotget-password/'.$this->user->resetPasswordToken), $html_content );



        $email_template_wrapper = EmailTemplateWrapper::where([
            "id" => $email_content->wrapper_id
        ])
        ->first();


        $html_final = json_decode($email_template_wrapper->template);
        $html_final =  str_replace("[content]", $html_content, $html_final);



        return $this->view('email.dynamic_mail',["html_content"=>$html_final]);
    }
}
