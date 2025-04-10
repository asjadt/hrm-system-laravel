<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
   private $data;
   private $type;
    public function __construct($data,$type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email_content = EmailTemplate::where([
            "type" => $this->type,
            "is_active" => 1

        ])->first();

        if(!$email_content){
            return $this->view('email.dummy');
        }


        $html_content = json_decode($email_content->template);
        $html_content =  str_replace("[customer_FirstName]", $this->data->customer->first_Name, $html_content );
        $html_content =  str_replace("[customer_LastName]", $this->data->customer->last_Name, $html_content );
        $html_content =  str_replace("[customer_FullName]", ($this->data->customer->first_Name. " " .$this->data->customer->last_Name), $html_content );


        $html_content =  str_replace("[business_owner_FirstName]", $this->data->business->owner->first_Name, $html_content );
        $html_content =  str_replace("[business_owner_LastName]", $this->data->business->owner->last_Name, $html_content );
        $html_content =  str_replace("[business_owner_FullName]", ($this->data->business->owner->first_Name. " " .$this->data->business->owner->last_Name), $html_content );

    $html_content =  str_replace("[automobile_make]", $this->data->automobile_make->name, $html_content );

    $html_content =  str_replace("[automobile_model]", $this->data->automobile_model->name, $html_content );

    $html_content =  str_replace("[car_registration_no]", $this->data->car_registration_no, $html_content );

    $html_content =  str_replace("[car_registration_year]", $this->data->car_registration_year, $html_content );

    $html_content =  str_replace("[status]", $this->data->status, $html_content );


    $html_content =  str_replace("[payment_status]", $this->data->payment_status, $html_content );

    $html_content =  str_replace("[additional_information]", $this->data->additional_information, $html_content );


    $html_content =  str_replace("[discount_type]", $this->data->discount_type, $html_content );

    $html_content =  str_replace("[discount_amount]", $this->data->discount_amount, $html_content );

    $html_content =  str_replace("[price]", $this->data->price, $html_content );

    $html_content =  str_replace("[job_start_date]", $this->data->job_start_date, $html_content );

    $html_content =  str_replace("[job_start_time]", $this->data->job_start_time, $html_content );

    $html_content =  str_replace("[job_end_time]", $this->data->job_end_time, $html_content );

    $html_content =  str_replace("[coupon_code]", $this->data->coupon_code, $html_content );

    $html_content =  str_replace("[fuel]", $this->data->fuel, $html_content );

    $html_content =  str_replace("[transmission]", $this->data->transmission, $html_content );



        $email_template_wrapper = EmailTemplateWrapper::where([
            "id" => $email_content->wrapper_id
        ])
        ->first();


        $html_final = json_decode($email_template_wrapper->template);
        $html_final =  str_replace("[content]", $html_content, $html_final);




        return $this->view('email.dynamic_mail',["html_content"=>$html_final]);
    }
}
