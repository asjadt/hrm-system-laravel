<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS - Forgot Password</title>
</head>
<body style="font-family: Arial, sans-serif;">

<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td style="background-color: #f5f5f5; padding: 20px;">
            <table align="center" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <tr>
                    <td style="padding: 40px 30px; text-align: center;">
                        <h2 style="color: #333333;">HRMS - Forgot Password</h2>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 30px;">
                        <p style="color: #666666; line-height: 1.6;">You've requested to reset your password. To reset your password, please click the button below:</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 30px; text-align: center;">
                        <a href="{{$url}}" style="display: inline-block; background-color: #007bff; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px;">Reset Password</a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 30px;">
                        <p style="color: #666666; line-height: 1.6;">Alternatively, you can copy and paste the following link into your browser:</p>
                        <p style="color: #666666; line-height: 1.6;">{{$url}}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 30px;">
                        <p style="color: #666666; line-height: 1.6;">If you did not request to reset your password, you can safely ignore this email. Your password will remain unchanged.</p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #007bff; padding: 20px 30px; border-radius: 0 0 10px 10px;">
                        <p style="color: #ffffff; margin: 0;">Thank you, <br> The HRMS Team</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
