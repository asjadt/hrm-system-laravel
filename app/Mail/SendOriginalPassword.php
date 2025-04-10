<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOriginalPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public $password;
    public function __construct($user,$password)
    {
        $this->user = $user;
        $this->password = $password;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {



     $front_end_url = env('FRONT_END_URL_DASHBOARD');

     $password_reset_link =  ($front_end_url.'/auth/change-password?token='.$this->user->resetPasswordToken);


        return $this->subject(("Welcome to " . env("APP_NAME") .  " - Set Your Password") . env("APP_NAME"))->view('email.send-original-password',[
            "user" => $this->user,
            "password_reset_link" => $password_reset_link,
            "password" => $this->password
        ]);
    }
}
