<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("type");
            $table->text("template");
            $table->boolean("is_active");


            $table->unsignedBigInteger("wrapper_id");
            $table->foreign('wrapper_id')->references('id')->on('email_template_wrappers')->onDelete('restrict');
            $table->timestamps();
        });
        DB::table('email_templates')->insert(
            array(
                [
                    'wrapper_id' => 1,
                    'type' => 'email_verification_mail',
                    "template"=> json_encode("<!DOCTYPE html>\n<html>\n<head>\n\n  <meta charset=\"utf-8\">\n  <meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">\n  <title>Email Confirmation</title>\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n  <style type=\"text/css\">\n  /**\n   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.\n   */\n  @media screen {\n    @font-face {\n      font-family: 'Source Sans Pro';\n      font-style: normal;\n      font-weight: 400;\n      src: local('Source Sans Pro Regular'), local('SourceSansPro-Regular'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format('woff');\n    }\n    @font-face {\n      font-family: 'Source Sans Pro';\n      font-style: normal;\n      font-weight: 700;\n      src: local('Source Sans Pro Bold'), local('SourceSansPro-Bold'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format('woff');\n    }\n  }\n  /**\n   * Avoid browser level font resizing.\n   * 1. Windows Mobile\n   * 2. iOS / OSX\n   */\n  body,\n  table,\n  td,\n  a {\n    -ms-text-size-adjust: 100%; /* 1 */\n    -webkit-text-size-adjust: 100%; /* 2 */\n  }\n  /**\n   * Remove extra space added to tables and cells in Outlook.\n   */\n  table,\n  td {\n    mso-table-rspace: 0pt;\n    mso-table-lspace: 0pt;\n  }\n  /**\n   * Better fluid images in Internet Explorer.\n   */\n  img {\n    -ms-interpolation-mode: bicubic;\n  }\n  /**\n   * Remove blue links for iOS devices.\n   */\n  a[x-apple-data-detectors] {\n    font-family: inherit !important;\n    font-size: inherit !important;\n    font-weight: inherit !important;\n    line-height: inherit !important;\n    color: inherit !important;\n    text-decoration: none !important;\n  }\n  /**\n   * Fix centering issues in Android 4.4.\n   */\n  div[style*=\"margin: 16px 0;\"] {\n    margin: 0 !important;\n  }\n  body {\n    width: 100% !important;\n    height: 100% !important;\n    padding: 0 !important;\n    margin: 0 !important;\n  }\n  /**\n   * Collapse table borders to avoid space between cells.\n   */\n  table {\n    border-collapse: collapse !important;\n  }\n  a {\n    color: #1a82e2;\n  }\n  img {\n    height: auto;\n    line-height: 100%;\n    text-decoration: none;\n    border: 0;\n    outline: none;\n  }\n  </style>\n\n</head>\n<body style=\"background-color: #e9ecef;\">\n\n  <!-- start preheader -->\n  <div class=\"preheader\" style=\"display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;\">\n    A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.\n  </div>\n  <!-- end preheader -->\n\n  <!-- start body -->\n  <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n\n    <!-- start logo -->\n    <tr>\n      <td align=\"center\" bgcolor=\"#e9ecef\">\n        <!--[if (gte mso 9)|(IE)]>\n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\">\n        <tr>\n        <td align=\"center\" valign=\"top\" width=\"600\">\n        <![endif]-->\n        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"max-width: 600px;\">\n          <tr>\n            <td align=\"center\" valign=\"top\" style=\"padding: 36px 24px;\">\n              <a href=\"https://www.blogdesire.com\" target=\"_blank\" style=\"display: inline-block;\">\n                <img src=\"https://www.blogdesire.com/wp-content/uploads/2019/07/blogdesire-1.png\" alt=\"Logo\" border=\"0\" width=\"48\" style=\"display: block; width: 48px; max-width: 48px; min-width: 48px;\">\n              </a>\n            </td>\n          </tr>\n        </table>\n        <!--[if (gte mso 9)|(IE)]>\n        </td>\n        </tr>\n        </table>\n        <![endif]-->\n      </td>\n    </tr>\n    <!-- end logo -->\n\n    <!-- start hero -->\n    <tr>\n      <td align=\"center\" bgcolor=\"#e9ecef\">\n        <!--[if (gte mso 9)|(IE)]>\n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\">\n        <tr>\n        <td align=\"center\" valign=\"top\" width=\"600\">\n        <![endif]-->\n        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"max-width: 600px;\">\n          <tr>\n            <td align=\"left\" bgcolor=\"#ffffff\" style=\"padding: 36px 24px 0; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; border-top: 3px solid #d4dadf;\">\n              <h1 style=\"margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;\">Confirm Your Email Address</h1>\n            </td>\n          </tr>\n        </table>\n        <!--[if (gte mso 9)|(IE)]>\n        </td>\n        </tr>\n        </table>\n        <![endif]-->\n      </td>\n    </tr>\n    <!-- end hero -->\n\n    <!-- start copy block -->\n    <tr>\n      <td align=\"center\" bgcolor=\"#e9ecef\">\n        <!--[if (gte mso 9)|(IE)]>\n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\">\n        <tr>\n        <td align=\"center\" valign=\"top\" width=\"600\">\n        <![endif]-->\n        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"max-width: 600px;\">\n\n          <!-- start copy -->\n          <tr>\n            <td align=\"left\" bgcolor=\"#ffffff\" style=\"padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;\">\n              <p style=\"margin: 0;\">Tap the button below to confirm your email address. If you didn't create an account with <a href=\"https://blogdesire.com\">Paste</a>, you can safely delete this email.</p>\n            </td>\n          </tr>\n          <!-- end copy -->\n\n          <!-- start button -->\n          <tr>\n            <td align=\"left\" bgcolor=\"#ffffff\">\n              <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n                <tr>\n                  <td align=\"center\" bgcolor=\"#ffffff\" style=\"padding: 12px;\">\n                    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n                      <tr>\n                        <td align=\"center\" bgcolor=\"#1a82e2\" style=\"border-radius: 6px;\">\n                          <a href=\"[AccountVerificationLink]\" target=\"_blank\" style=\"display: inline-block; padding: 16px 36px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; border-radius: 6px;\">Do Something Sweet</a>\n                        </td>\n                      </tr>\n                    </table>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <!-- end button -->\n\n          <!-- start copy -->\n          <tr>\n            <td align=\"left\" bgcolor=\"#ffffff\" style=\"padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;\">\n              <p style=\"margin: 0;\">If that doesn't work, copy and paste the following link in your browser:</p>\n              <p style=\"margin: 0;\"><a href=\"[AccountVerificationLink]\" target=\"_blank\">[AccountVerificationLink]</a></p>\n            </td>\n          </tr>\n          <!-- end copy -->\n\n          <!-- start copy -->\n          <tr>\n            <td align=\"left\" bgcolor=\"#ffffff\" style=\"padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf\">\n              <p style=\"margin: 0;\">Cheers,<br> Paste</p>\n            </td>\n          </tr>\n          <!-- end copy -->\n\n        </table>\n        <!--[if (gte mso 9)|(IE)]>\n        </td>\n        </tr>\n        </table>\n        <![endif]-->\n      </td>\n    </tr>\n    <!-- end copy block -->\n\n    <!-- start footer -->\n    <tr>\n      <td align=\"center\" bgcolor=\"#e9ecef\" style=\"padding: 24px;\">\n        <!--[if (gte mso 9)|(IE)]>\n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\">\n        <tr>\n        <td align=\"center\" valign=\"top\" width=\"600\">\n        <![endif]-->\n        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"max-width: 600px;\">\n\n          <!-- start permission -->\n          <tr>\n            <td align=\"center\" bgcolor=\"#e9ecef\" style=\"padding: 12px 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; color: #666;\">\n              <p style=\"margin: 0;\">You received this email because we received a request for [type_of_action] for your account. If you didn't request [type_of_action] you can safely delete this email.</p>\n            </td>\n          </tr>\n          <!-- end permission -->\n\n          <!-- start unsubscribe -->\n          <tr>\n            <td align=\"center\" bgcolor=\"#e9ecef\" style=\"padding: 12px 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; color: #666;\">\n              <p style=\"margin: 0;\">To stop receiving these emails, you can <a href=\"https://www.blogdesire.com\" target=\"_blank\">unsubscribe</a> at any time.</p>\n              <p style=\"margin: 0;\">Paste 1234 S. Broadway St. City, State 12345</p>\n            </td>\n          </tr>\n          <!-- end unsubscribe -->\n\n        </table>\n        <!--[if (gte mso 9)|(IE)]>\n        </td>\n        </tr>\n        </table>\n        <![endif]-->\n      </td>\n    </tr>\n    <!-- end footer -->\n\n  </table>\n  <!-- end body -->\n\n</body>\n</html>"),
                    "is_active" => 1
                ],
                [
                    'wrapper_id' => 1,
                    'type' => 'forget_password_mail',
                    "template"=> ('
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Reset Your HR Management Software Password</title>
                    </head>
                    <body>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                            <tr>
                                <td align="center" bgcolor="#f8f9fa" style="padding: 40px 0;">
                                    <h1>Reset Your HR Management Software Password</h1>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" bgcolor="#ffffff" style="padding: 40px 0;">
                                    <p>Dear [FirstName] [MiddleName] [LastName],</p>
                                    <p>We noticed that you requested to reset your password for accessing our HR Management Software. To ensure the security of your account, please follow the instructions below to reset your password:</p>
                                    <ol>
                                        <li>Click on the following link to reset your password: <a href="[ForgotPasswordLink]">Reset Password</a></li>
                                        <li>If the link does not work, please copy and paste the following URL into your browser\'s address bar: [Reset Password URL]</li>
                                        <li>You will be directed to a page where you can create a new password. Make sure to choose a strong password that contains a combination of letters, numbers, and special characters.</li>
                                        <li>Once your password has been successfully reset, you can log in to your account using your new credentials.</li>
                                    </ol>
                                    <p>If you did not request this password reset, please disregard this email. Your account is secure, and no changes have been made.</p>
                                    <p>If you have any questions or need further assistance, please don\'t hesitate to contact our support team at <a href="mailto:asjadtariq@gmail.com">asjadtariq@gmail.com</a>.</p>
                                    <p>Thank you for using our HR Management Software.</p>
                                    <p>Best regards,<br>[Your Company Name] Team</p>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>


                    '),
                    "is_active" => 1
                ],

                [
                    'wrapper_id' => 1,
                    'type' => 'welcome_message',
                    "template"=>json_encode("<html lang=\"en\">\n<head>\n\t<meta charset=\"utf-8\" />\n\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\" />\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n\t<title></title>\n\t<link href='https://fonts.googleapis.com/css?family=Lato:300,400|Montserrat:700' rel='stylesheet' type='text/css'>\n\t<style>\n\t\t@import url(//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.1/normalize.min.css);\n\t\t@import url(//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css);\n\t</style>\n\t<link rel=\"stylesheet\" href=\"https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/default_thank_you.css\">\n\t<script src=\"https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/jquery-1.9.1.min.js\"></script>\n\t<script src=\"https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/html5shiv.js\"></script>\n</head>\n<body>\n\t<header class=\"site-header\" id=\"header\">\n\t\t<h1 class=\"site-header__title\" data-lead-id=\"site-header-title\">THANK YOU!</h1>\n\t</header>\n\n\t<div class=\"main-content\">\n\t\t<i class=\"fa fa-check main-content__checkmark\" id=\"checkmark\"></i>\n\t\t<p class=\"main-content__body\" data-lead-id=\"main-content-body\">Thanks a bunch for filling that out. It means a lot to us, just like you do! We really appreciate you giving us a moment of your time today. Thanks for being you.</p>\n\t</div>\n\n\t<footer class=\"site-footer\" id=\"footer\">\n\t\t<p class=\"site-footer__fineprint\" id=\"fineprint\">Copyright ©2014 | All Rights Reserved</p>\n\t</footer>\n</body>\n</html>"),
                    "is_active" => 1
                ],


            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
}
