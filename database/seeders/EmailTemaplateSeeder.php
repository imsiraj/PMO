<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplates;
use Carbon\Carbon;

class EmailTemaplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailTemplates::insert([
            [
                'title' => 'User Registration',
                'subject' => env('APP_NAME').' : Registration',
                'content' => '<div class=es-wrapper-color style=background-color:#f4f4f4><table cellpadding=0 cellspacing=0 style="mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;padding:0;margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top"width=100% class=es-wrapper><tr style=border-collapse:collapse><td style=padding:0;margin:0 valign=top><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;table-layout:fixed!important;width:100% class=es-content align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;background-color:transparent width=600 class=es-content-body align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=left><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center valign=top width=600><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:separate;border-spacing:0;border-radius:4px;background-color:#fff width=100% bgcolor=#ffffff><tr style=border-collapse:collapse><td style="margin:0;padding:20px 30px 20px 30px"align=left bgcolor=#ffffff class=es-m-txt-l><p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758>We are excited to have you join us. Your account has now been registered and we are making sure you are who you say you are. Just a few details for us to check. <p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758> <p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758>Please allow us up to 48hrs to activate your account, although its usually done much quicker. <p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758> <p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758><span style="color:#353758;font-family:lato,"helvetica neue",helvetica,arial,sans-serif;font-size:18px">If you have any questions, just reply to this email—we are always happy to help out.</span><p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758> <p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758><span style="color:#353758;font-family:lato,"helvetica neue",helvetica,arial,sans-serif;font-size:18px">{APP_NAME} Team.</span></table></table></table></table><table cellpadding=0 cellspacing=0 style="mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;table-layout:fixed!important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top"class=es-footer align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;background-color:transparent width=600 class=es-footer-body align=center><tr style=border-collapse:collapse><td style=margin:0;padding:30px align=left><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center valign=top width=540><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=left><p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato,helvetica,arial,sans-serif;line-height:21px><strong><a href={PORTAL_URL} rel=noopener style=-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline target=_blank>Home</a> - <a href={PORTAL_URL}/about rel=noopener style=-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline target=_blank>About </a>- <a href={PORTAL_URL}/contact-us rel=noopener style=-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline target=_blank>Contact Us</a></strong><tr style=border-collapse:collapse><td style=padding:0;margin:0;padding-top:25px align=left><p style=margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato,helvetica,arial,sans-serif;line-height:21px;color:#666>You received this email because you just registered with us. If you were not expecting this, please let us know by using the link above.</table></table></table></table></table></div>',
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'User Forgot Password',
                'subject' => env('APP_NAME').' : Forgot Password',
                'content' => '<div class=es-wrapper-color style=background-color:#f4f4f4><table cellpadding=0 cellspacing=0 style="mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;padding:0;margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top"width=100% class=es-wrapper><tr style=border-collapse:collapse><td style=padding:0;margin:0 valign=top><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;table-layout:fixed!important;width:100% class=es-content align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;background-color:transparent width=600 class=es-content-body align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=left><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center valign=top width=600><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:separate;border-spacing:0;border-radius:4px;background-color:#fff width=100% bgcolor=#ffffff><tr style=border-collapse:collapse><td style="margin:0;padding:20px 30px 20px 30px"align=left bgcolor=#ffffff class=es-m-txt-l><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758">Nevermind, lets get you back in. Click on the link below to reset your password. <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"> <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#000"align=center><span style="background:#b7b7b7de;display:inline-block;border-radius:2px;width:auto;border:1px solid #353758"class=es-button-border><a href={RESET_PASSWORD_LINK} rel=noopener style="mso-style-priority:100!important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica,arial,verdana,sans-serif;font-size:20px;color:#000;border-style:solid;border-color:#b7b7b7de;border-width:15px 30px;display:inline-block;background:#b7b7b7de;border-radius:2px;font-weight:400;font-style:normal;line-height:24px;width:auto;text-align:center"target=_blank class=es-button>Reset Password</a></span><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"> <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"><span style="color:#353758;font-family:lato,"helvetica neue",helvetica,arial,sans-serif;font-size:18px">If you have any questions, just reply to this email—we are always happy to help out.</span><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"> <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"><span style="color:#353758;font-family:lato,"helvetica neue",helvetica,arial,sans-serif;font-size:18px">{APP_NAME} Team.</span></table></table></table></table><table cellpadding=0 cellspacing=0 style="mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;table-layout:fixed!important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top"class=es-footer align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;background-color:transparent width=600 class=es-footer-body align=center><tr style=border-collapse:collapse><td style=margin:0;padding:30px align=left><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center valign=top width=540><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=left><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato,helvetica,arial,sans-serif;line-height:21px"><strong><a href={PORTAL_URL} rel=noopener style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline"target=_blank>Home</a> - <a href={PORTAL_URL}/about rel=noopener style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline"target=_blank>About </a>- <a href={PORTAL_URL}/contact-us rel=noopener style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline"target=_blank>Contact Us</a></strong><tr style=border-collapse:collapse><td style=padding:0;margin:0;padding-top:25px align=left><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato,helvetica,arial,sans-serif;line-height:21px;color:#666">You received this email because you just registered with us. If you were not expecting this, please let us know by using the link above.</table></table></table></table></table></div>',
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Email Verification',
                'subject' => env('APP_NAME').' : Email Verification',
                'content' => '<div class=es-wrapper-color style=background-color:#f4f4f4><table cellpadding=0 cellspacing=0 style="mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;padding:0;margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top"width=100% class=es-wrapper><tr style=border-collapse:collapse><td style=padding:0;margin:0 valign=top><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;table-layout:fixed!important;width:100% class=es-content align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;background-color:transparent width=600 class=es-content-body align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=left><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center valign=top width=600><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:separate;border-spacing:0;border-radius:4px;background-color:#fff width=100% bgcolor=#ffffff><tr style=border-collapse:collapse><td style="margin:0;padding:20px 30px 20px 30px"align=left bgcolor=#ffffff class=es-m-txt-l><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758">Hi {FULL_NAME},</p><br/><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758">We are excited to have you with us! Please verify your email address by clicking the link below:. <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"> <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#000"align=center><span style="background:#b7b7b7de;display:inline-block;border-radius:2px;width:auto;border:1px solid #353758"class=es-button-border><a href={VERIFY_EMAIL_LINK} rel=noopener style="mso-style-priority:100!important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica,arial,verdana,sans-serif;font-size:20px;color:#000;border-style:solid;border-color:#b7b7b7de;border-width:15px 30px;display:inline-block;background:#b7b7b7de;border-radius:2px;font-weight:400;font-style:normal;line-height:24px;width:auto;text-align:center"target=_blank class=es-button>Verify Email</a></span><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"> <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"><span style="color:#353758;font-family:lato,"helvetica neue",helvetica,arial,sans-serif;font-size:18px">If you have any questions, just reply to this email—we are always happy to help out.</span><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"> <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato,helvetica,arial,sans-serif;line-height:27px;color:#353758"><span style="color:#353758;font-family:lato,"helvetica neue",helvetica,arial,sans-serif;font-size:18px">{APP_NAME} Team.</span></table></table></table></table><table cellpadding=0 cellspacing=0 style="mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;table-layout:fixed!important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top"class=es-footer align=center><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0;background-color:transparent width=600 class=es-footer-body align=center><tr style=border-collapse:collapse><td style=margin:0;padding:30px align=left><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=center valign=top width=540><table cellpadding=0 cellspacing=0 style=mso-table-lspace:0;mso-table-rspace:0;border-collapse:collapse;border-spacing:0 width=100%><tr style=border-collapse:collapse><td style=padding:0;margin:0 align=left><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato,helvetica,arial,sans-serif;line-height:21px"><strong><a href={PORTAL_URL} rel=noopener style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline"target=_blank>Home</a> - <a href={PORTAL_URL}/about rel=noopener style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline"target=_blank>About </a>- <a href={PORTAL_URL}/contact-us rel=noopener style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato,helvetica,arial,sans-serif;font-size:14px;text-decoration:underline"target=_blank>Contact Us</a></strong><tr style=border-collapse:collapse><td style=padding:0;margin:0;padding-top:25px align=left><p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato,helvetica,arial,sans-serif;line-height:21px;color:#666">You received this email because you just registered with us. If you were not expecting this, please let us know by using the link above.</table></table></table></table></table></div>',
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
        
    }
}
