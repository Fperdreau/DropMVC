<?php
/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of DropCMS.
 *
 * DropCMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropCMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DropCMS.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Core\Mail;

// Import html2text class
use Core\Database\Database;
use \Core\Database\MySqlDb;
use Core\Settings;
use PHPMailer;

require_once(PATH_TO_LIBS . "html2text-0.2.2/html2text.php");
require_once(PATH_TO_LIBS . 'PHPMailer/class.phpmailer.php');
require_once(PATH_TO_LIBS . 'PHPMailer/class.smtp.php');

/**
 * Class Mail
 * @package Core\Mail
 */
class Mail {

    /**
     * @var Database $db
     */
    protected $db;

    /**
     * @@var Settings $config
     */
    public $config;

    /**
     * Mail settings
     * @var array|null
     */
    public $settings = array(
        'mail_from'=>"name@your-domain.com",
        'mail_from_name'=>"your_name",
        'mail_host'=>"host address",
        'mail_port'=>"465",
        'mail_username'=>"",
        'mail_password'=>"",
        'SMTP_secure'=>'ssl',
        'pre_header'=>'[your_header]'
    );

    /**
     * @var bool
     */
    private $SMTPDebug = false;

    /**
     * Class constructor
     */
    function __construct() {
        $this->db = MySqlDb::getInstance();
        $this->config = new Settings($this->db, 'Mail', $this->settings);
        $this->settings = $this->config->settings;
    }

    /**
     * Send an email
     * @param $to
     * @param $subject
     * @param $body
     * @param null $attachment
     * @return bool
     */
    function send_mail($to,$subject,$body,$attachment = null) {
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->IsSMTP();                                      // set mailer to use SMTP
        $mail->SMTPDebug  = $this->SMTPDebug;         // enables SMTP debug information (for testing)

        $mail->Mailer = "smtp";
        $mail->Host = $this->config->settings['mail_host'];
        $mail->Port = $this->config->settings['mail_port'];

        if ($this->config->settings['SMTP_secure'] !== "none") {
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->SMTPSecure = $this->config->settings['SMTP_secure']; // secure transfer enabled REQUIRED for GMail
            $mail->Username = $this->config->settings['mail_username'];
            $mail->Password = $this->config->settings['mail_password'];
        }

        $mail->From = $this->config->settings['mail_from'];
        $mail->FromName = $this->config->settings['mail_from_name'];

        $mail->AddAddress("undisclosed-recipients:;");
        $mail->AddReplyTo($this->config->settings['mail_from'], $this->config->settings['mail_from_name']);

        if (is_array($to)) {
            foreach($to as $to_add){
                $mail->AddBCC($to_add);                  // name is optional
            }
        } else {
            $mail->AddBCC($to);                  // name is optional
        }

        $mail->WordWrap = 50;                                 // set word wrap to 50 characters
        $mail->IsHTML(true);

        $mail->Subject = $this->config->settings['pre_header']." ".$subject;
        $mail->Body    = $body;
        $mail->AltBody= @convert_html_to_text($body); // Convert to plain text for email viewers non-compatible with HTML content

        if(!is_null($attachment)){
            if (!is_array($attachment)) $attachment = array($attachment);
            foreach ($attachment as $path) {
                $split = explode('/', $path);
                $file_name = end($split);
                if (!$mail->AddAttachment($path, $file_name)) {
                    return false;
                }
            }
        }

        if ($rep = $mail->Send()) {
            $mail->ClearAddresses();
            $mail->ClearAttachments();
            return true;
        } else {
            return false;
        }

    }

}
