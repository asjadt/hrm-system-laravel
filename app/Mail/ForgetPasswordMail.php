<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */



    private $user;
    private $client_site;





    public function __construct($user=null,$client_site = "")
    {

        $this->user = $user;

        $this->client_site = $client_site;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {



        $email_content = EmailTemplate::where([
            "type" => "forget_password_mail",
            "is_active" => 1

        ])->first();


        $html_content = json_decode($email_content->template);
        $html_content =  str_replace("[FirstName]", $this->user->first_Name, $html_content );
        $html_content =  str_replace("[LastName]", $this->user->last_Name, $html_content );
        $html_content =  str_replace("[FullName]", ($this->user->first_Name. " " .$this->user->last_Name), $html_content );

        $html_content =  str_replace("[AccountVerificationLink]", (env('APP_URL').'/activate/'.$this->user->email_verify_token), $html_content);


        if($this->client_site == "client") {
               $front_end_url = env('FRONT_END_URL_CLIENT');
        } else if($this->client_site == "dashboard") {
               $front_end_url = env('FRONT_END_URL_DASHBOARD');
        }





        $html_content =  str_replace("[ForgotPasswordLink]", ($front_end_url.'/auth/change-password?token='.$this->user->resetPasswordToken), $html_content );



        $email_template_wrapper = EmailTemplateWrapper::where([
            "id" => $email_content->wrapper_id
        ])
        ->first();


        $html_final = json_decode($email_template_wrapper->template);
        $html_final =  str_replace("[content]", $html_content, $html_final);



        return $this->view('email.forget_password',[
            "url" => ($front_end_url.'/auth/change-password?token='.$this->user->resetPasswordToken)
        ]);



        return $this->view('email.dynamic_mail',["html_content"=>$html_final]);


    }
}
