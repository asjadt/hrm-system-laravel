<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait BasicEmailUtil
{
    public function emailConfigure($user=NULL){

        if(empty($user)){
            $user = auth()->user();
        }

        if (!empty($user)) {

            $business = $user->business;
            if (!empty($business) && !empty($business->emailSettings)) {

                $emailSettings = $business->emailSettings;

                Config::set('mail.driver', $emailSettings->mail_driver);
                Config::set('mail.host', $emailSettings->mail_host);
                Config::set('mail.port', $emailSettings->mail_port);
                Config::set('mail.username', $emailSettings->mail_username);
                Config::set('mail.password', $emailSettings->mail_password);
                Config::set('mail.encryption', $emailSettings->mail_encryption);
                Config::set('mail.from.address', $emailSettings->mail_from_address);
                Config::set('mail.from.name', $emailSettings->mail_from_name);


            }
        }

    }

    public function convertBladeToString($template)
    {
        $template = preg_replace('/{{\s*([a-zA-Z0-9_]+)\s*}}/', '{{ $1 }}', $template);
        $template = preg_replace('/{!!\s*([a-zA-Z0-9_]+)\s*!!}/', '{{ $1 }}', $template);
        $template = str_replace('<?=', '{{', $template);
        $template = str_replace('?>', '}}', $template);
        $template = str_replace('"', '\\"', $template);
        $template = str_replace("\n", '', $template);
        $template = str_replace("\r", '', $template);
        return $template;
    }

    function extractVariables($string) {
        preg_match_all('/\[(.*?)\]/', $string, $matches);
        return $matches[1];
    }


    public function prepareEmailTemplateData($template_type,$business_id) {
        $template_string = view(('email.' . $template_type))->render();

        $template = $this->convertBladeToString($template_string);
        $templateVariables = $this->extractVariables($template);
        $email_content = [
            "name" => "",
            "type" => $template_type,
            "is_active" => 1,
            "wrapper_id" => 1,
            "is_default" => empty($business_id)?1:0,
            "business_id" => $business_id,
            "template" => $template,
            "template_variables" => implode(',', $templateVariables),
            "created_at" => now(),
            "updated_at" => now()

        ];

        return $email_content;

    }





    public function storeEmailTemplateIfNotExists($type,$business_id,$is_default,$customForBusiness) {

        $templateString = view(('email.' . $type))->render();
 
        $template = $this->convertBladeToString($templateString);
        $templateVariables = $this->extractVariables($template);

        error_log("template creating");

        $email_content = EmailTemplate::create(
            [
                "name" => "",
                "type" => $type,
                "is_active" => 1,
                "wrapper_id" => 1,
                "business_id" => $business_id,
                "is_default" => $is_default,

                "template" => $template,
                "template_variables" => implode(',', $templateVariables)

            ]
        );


        if($customForBusiness && empty($business_id)){

                $business_ids = Business::pluck("id");

                $email_templates = $business_ids->map(function ($business_id) use ($is_default, $template, $templateVariables, $type) {
                    return [
                        "name" => "",
                        "type" => $type,
                        "is_active" => 1,
                        "wrapper_id" => 1,
                        "business_id" => $business_id,
                        "is_default" => $is_default,

                        "template" => $template,
                        "template_variables" => implode(',', $templateVariables),
                        "created_at" => now(),
                        "updated_at" => now(),
                    ];
                });
                error_log("template creating 2");
                EmailTemplate::insert($email_templates->toArray());
            }


        return $email_content;
    }
}
