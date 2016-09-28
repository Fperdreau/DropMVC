<?php
/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of JR-Haarsieraad.
 *
 * JR-Haarsieraad is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JR-Haarsieraad is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with JR-Haarsieraad.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Views;


use App\App;
use App\Entity\MailEntity;
use App\Entity\UsersEntity;

/**
 * Class MailView
 * @package App\Views
 */
class MailView {

    /**
     * Show Mail settings
     * @param \Core\HTML\Form $form
     * @return string
     */
    public function settings($form) {
        $formContent = "<div class='submit_btns'>" . $form->submit('processform') . "</div>";
        $formContent .= $form->input('mail_from','Sender email',['type'=>'text']);
        $formContent .= $form->input('mail_from_name','Sender name',['type'=>'text']);
        $formContent .= $form->input('mail_host','Email host',['type'=>'text']);
        $formContent .= $form->select('SMTP_secure','SMTP',['SSL'=>'ssl','TLS'=>'tls','None'=>'none']);
        $formContent .= $form->input('mail_port','Email port',['type'=>'text']);
        $formContent .= $form->input('mail_username','Email username',['type'=>'text']);
        $formContent .= $form->input('mail_password','Email password',['type'=>'password']);
        $formContent .= $form->input('pre_header','Header prefix',['type'=>'text']);

        $content = "
            $formContent
            <div class='feedback' id='feedback_mail'></div>
        ";
        return $content;
    }

    /**
     * Renders contact form
     * @return string
     */
    public static function contactForm() {
        $url = URL_TO_APP . "contact/send";
        return "
        <form method='post' action='{$url}'>
            <input type='hidden' name='contact_send' value='true'/>
            <fieldset class='form-group'>
                <label>"._('Name')."</label>
                <input type='text' name='name' placeholder='"._('Your name')."' style='width: 250px' required/>
            </fieldset>
            <fieldset class='form-group'>
                <label>"._('Email')."</label>
                <input type='email' name='email' placeholder='"._('Your email')."' style='width: 250px' required autocomplete='on'/>
            </fieldset>

            <fieldset class='form-group'>
                <label>"._('Subject')."</label>
                <input type='text' name='subject' id='contact_subject' placeholder='"._('Subject')."' style='width: 250px' required/>
            </fieldset>

            <fieldset class='form-group'>
                <label>"._('Message')."</label>
                <textarea name='message' placeholder='"._('Your Message')."' required></textarea>
            </fieldset>
            <fieldset class='submit_btns' style='width: 50%;'>
                <input type='submit' name='send' value='"._('Send')."' class='processform' />
            </fieldset>
        </form>

        ";
    }

    /**
     * Template for email sent via the Contact page
     * @param $post
     * @return mixed
     */
    public static function contactAdmin($post) {
        $content['body'] = "
        <div>
            <p>Hello,</p>
            <p>Please find below a message posted by <strong>{$post['name']}</strong>.</p>
            <p>You can contact this person at this email: {$post['email']}</p>
        </div>

        <div>
            <h1>Message</h1>
            {$post['message']}
        </div>
        ";
        $content['subject'] = "New message: ".$post['subject'];
        return $content;
    }

    /**
     * Email template email (html)
     * @param $content
     * @param $email
     * @param null $email_id
     * @return string
     */
    public static function template($content, $email, $email_id=null) {
        $show_in_browser = (is_null($email_id)) ? null:
            "<a href='" . URL_TO_APP . "mail/show/{$email_id}"
            . "' target='_blank' style='color: #CF5151; text-decoration: none;'>". _('View this email in your browser') . "</a>";
        $url = URL_TO_APP.'newsletter/unsubscribe/'.$email;
        $body = "
            <div style='box-sizing: border-box; font-family: Ubuntu, Helvetica, Arial, sans-serif sans-serif; color: #444444;
             font-weight: 300; font-size: 14px; width: 100%; height: auto; margin: 0;'>
                <div style='line-height: 1.2; min-width: 320px; width: 70%;  margin: 50px auto 0 auto;'>
                    <div style='text-align: center;'>{$show_in_browser}</div>
 
                    <div style='padding:20px;  margin: 2% auto; width: 100%; background-color: #F9F9F9; border: 1px solid #e0e0e0;
                    font-size: 2em; line-height: 40px; text-align: center; box-sizing: border-box;'>
                        <div id='sitelogo'>
                            <div style='width: auto; vertical-align: middle; display: inline-block;'>
                            <img style='height: 50px; width: auto; vertical-align: middle;' src='" . URL_TO_APP . "public/images/weblogo.png' alt='logo'></div>
                            <div style='width: auto; vertical-align: middle; display: inline-block;' id='sitetitle'>
                                <div>
                                    <div style='font-size: 14px; font-weight: 400;'>" . App::getInstance()->getSettings('siteTitle') . "</div>
                                    <div style='font-size: 10px; font-weight: 300; font-style: oblique;' id='subtitle'>
                                        " . App::getInstance()->getSettings('siteSubTitle') . "
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style='padding:20px; margin: 2% auto; width: 100%; background-color: #F9F9F9; border: 1px solid #e0e0e0;
                    text-align: justify;  box-sizing: border-box;'>
                        $content
                    </div>

                    <div style='padding:20px; margin: 2% auto; width: 100%; border: 1px solid #e0e0e0; min-height: 30px;
                    height: auto; line-height: 30px; text-align: center; background-color: #444444; color: #ffffff; box-sizing: border-box;'>
                        "._('This email has been sent automatically. If you do not wish to receive emails from me')."
                        <a href='{$url}' style='color: #CF5151; text-decoration: none;' target='_blank' >"._('click here')."</a>.
                    </div>
                </div>
            </div>";
        return $body;
    }

    /**
     * Display email
     * @param MailEntity $email: email information
     * @param array $attachments: list of files name attached to this email
     * @return string
     */
    public static function showEmail(MailEntity $email, array $attachments=array()) {
        $file_list = "";
        if (!empty($attachments)) {
            foreach ($attachments as $file_name) {
                $file_list .= "<div class='email_file'><a href='" . URL_TO_APP . 'uploads/' . $file_name . "'>{$file_name}</a></div>";
            }
            $file_list = "<div class='email_header'>Files list:</div>{$file_list}";
        }

        $content = htmlspecialchars_decode($email->content);
        return "
        <div class='email_files'>{$file_list}</div>
        <div class='email_title'><span class='email_header'>Subject:</span> {$email->subject}</div>
        <div class='email_content'>{$content}</div>
        ";
    }

    /**
     * Send a verification email to organizers when someone signed up to the application.
     * @param UsersEntity $user
     * @return bool
     */
    function send_verification_mail(UsersEntity $user) {
        $result['subject'] = 'Signup | Verification'; // Give the email a subject
        $authorize_url = URL_TO_APP."Auth/verify/$user->email/$user->hash/true";
        $deny_url = URL_TO_APP."Auth/verify/$user->email/$user->hash/false";

        $content = "
        Hello,<br><br>
        <p><b>{$user->username}</b> wants to create an account.</p>
        <p><a href='$authorize_url'>Authorize</a></p>
        or
        <p><a href='$deny_url'>Deny</a></p>
        <p>Best regards</p>
        ";

        $result['body'] = self::template($content, $user->email);
        return $result;
    }

    /**
     * Send an email to the user if his/her account has been deactivated due to too many login attempts.
     * @param UsersEntity $user
     * @return bool
     */
    function send_activation_mail(UsersEntity $user) {
        $result['subject'] = 'Your account has been deactivated'; // Give the email a subject
        $authorize_url = URL_TO_APP."users/verify/$user->email/$user->hash/true";
        $newpwurl = URL_TO_APP."users/renew_pwd/$user->hash/$user->email";
        $content = "
        <p>Hello <b>$user->fullname</b>,</p>
        <p>We have the regret to inform you that your account has been deactivated due to too many login attempts.</p>
        <p>You can reactivate your account by following this link:<br>
        <a href='$authorize_url'>$authorize_url</a>
        </p>
        <p>If you forgot your password, you can ask for another one here:<br>
        <a href='$newpwurl'>$newpwurl</a>
        </p>
        <p>Best regards</p>
        ";

        $result['body'] = self::template($content, $user->email);
        return $result;
    }

    /**
     * Send a confirmation email to the new user once his/her registration has been validated by an organizer
     * @param $user
     * @return bool
     */
    function send_confirmation_mail(UsersEntity $user) {
        $result['subject'] = 'Signup | Confirmation'; // Give the email a subject
        $login_url = URL_TO_APP."admin";

        $content = "
        Hello $user->fullname,<br><br>
        Thank you for signing up!<br>
        <p>Your account has been created, you can now <a href='$login_url'>log in</a> with the following credentials.</p>
        <p>------------------------<br>
        <b>Username</b>: $user->username<br>
        <b>Password</b>: Only you know it!<br>
        ------------------------</p>
        <p>Best regards</p>
        ";
        $result['body'] = self::template($content, $user->email);
        return $result;
    }

    /**
     * Send an email to the user if his/her account has been deactivated due to too many login attempts.
     * @param UsersEntity $user
     * @return bool
     */
    function send_password_request(UsersEntity $user) {
        $result['subject'] = 'Password request'; // Give the email a subject
        $newpwurl = URL_TO_APP."users/renew_pwd/$user->hash/$user->email";
        $content = "
        <p>Hello <b>$user->fullname</b>,</p>

        <p>You have requested a new password. You can modify your password by following this link:</p>
        <a href='$newpwurl'>$newpwurl</a>
        </p>
        <p>Best regards</p>
        ";

        $result['body'] = self::template($content, $user->email);
        return $result;
    }

    public static function resultMsg($result) {
        if ($result) {
            return _('Message successfully sent');
        } else {
            return _('Sorry, your message could not be sent');
        }
    }
}