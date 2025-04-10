<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: Arial, sans-serif;
                color: #333;
                background-color: #f5f5f5;
                padding: 40px;
            }

            h1 {
                font-size: 24px;
                margin-bottom: 20px;
            }

            p {
                margin-bottom: 20px;
                font-size: 16px;
                line-height: 1.5;
            }

            a {
                color: #007bff;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <h1>Dear {{ $user->first_Name }},</h1>
        <p>Thank you for registering for our service. We're glad you've chosen to join us!</p>
        <p>To complete your registration and start using our services, we need to verify your email address. This is a quick and simple process that helps us ensure the security and privacy of your account.</p>
        <p>Please click the link below to verify your email address:</p>
        <p><a href="{{env('APP_URL')}}/activate/{{$user->email_verify_token}}">{{env('APP_URL')}}/activate/{{$user->email_verify_token}}</a></p>
        <p>If you cannot click the link, please copy and paste it into your browser's address bar.</p>
        <p>If you did not sign up for our service, please ignore this email. Your email address will not be used for any other purpose.</p>
        <p>If you have any questions or concerns, please do not hesitate to reach out to us at {{ $contactEmail }}. We're here to help!</p>
        <p>Best regards,</p>
        <p>{{env('APP_NAME')}}</p>
    </body>
</html>
