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

namespace App\Controller;


use App\Entity\UsersEntity;
use App\Model\MailModel;
use App\Model\UsersModel;
use App\Views\MailView;
use Core\Mail\Mail;

/**
 * Class MailController
 * @package App\Controller
 */
class MailController extends AppController {

    /**
     * @var Mail $MailAPI
     */
    private $MailAPI;

    /**
     * @var MailView
     */
    protected $view;

    /**
     * @var UsersModel
     */
    protected $Users;

    /**
     * @var MailModel
     */
    protected $Mail;

    /**
     * MailController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->MailAPI = new Mail();
        $this->loadModel('Users');
        $this->loadModel('Mail');
        $this->loadView('Mail');
    }

    /**
     * Send email
     * @param string|array $emails
     * @param null $message
     * @param null $subject
     * @param null $attachment
     * @return mixed
     */
    public function send($emails, $message=null, $subject=null, $attachment=null) {
        $message = (is_null($message)) ? htmlspecialchars_decode($_POST['message']):$message;
        $subject = (is_null($subject)) ? ($_POST['subject']):$subject;
        $result['status'] = false;
        $result['msg'] = "";

        $content['body'] = $message;
        $content['subject'] = $subject;
        $content['attachments'] = $attachment;

        if (!is_array($emails)) $emails = array($emails);
        foreach ($emails as $email) {
            $result = $this->saveAndSend($content, array($email));
            $result['msg'] = $this->view->resultMsg($result['status']);
        }

        return $result;

    }

    /**
     * Preview email content
     */
    public function preview() {
        $message = ($_POST['message']);
        $formattedContent = $this->view->template($message, '');
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($formattedContent);
        } else {
            echo $formattedContent;
        }
    }

    /**
     * Format email content
     * @param string $message
     * @return string
     */
    public function format($message) {
        return $this->view->template($message, '');
    }

    /**
     * Send a verification email to admins when a user signed up to the application.
     * @param $hash
     * @param $user_mail
     * @param $username
     * @return bool
     */
    function send_verification_mail($hash, $user_mail, $username) {
        $Users = new UsersController();
        $admins = $Users->getAdmin();
        $to = array();
        for ($i=0; $i<count($admins); $i++) {
            $to[] = $admins[$i]['email'];
        }
        $content = $this->view->send_verification_mail($hash, $user_mail, $username);

        return $this->MailAPI->send_mail($to,$content['subject'],$content['body']);
    }

    /**
     * Send an email to the user if his/her account has been deactivated due to too many login attempts.
     * @param UsersEntity $user
     * @return bool
     */
    function send_activation_mail(UsersEntity $user) {
        $to = $user->email;
        $content = $this->view->send_activation_mail($user);
        return $this->MailAPI->send_mail($to,$content['subject'],$content['body']);
    }

    /**
     * Send a confirmation email to the new user once his/her registration has been validated by an admin
     * @param UsersEntity $user
     * @return bool
     */
    function send_confirmation_mail(UsersEntity $user) {
        $content = $this->view->send_confirmation_mail($user);
        return $this->MailAPI->send_mail($user->email, $content['subject'], $content['body']);
    }

    /**
     * @param UsersEntity $user
     * @return bool
     */
    public function send_password_request(UsersEntity $user) {
        $content = $this->view->send_password_request($user);
        return $this->MailAPI->send_mail($user->email, $content['subject'], $content['body']);
    }

    /**
     * Add email to db
     * @param null|array $post
     * @return bool
     */
    public function add($post=null) {
        $post = (is_null($post)) ? $_POST:$post;
        $post['date'] = date('Y-m-d h:i:s'); // Date of creation
        $post['mail_id'] = $this->Mail->generateID('mail_id');
        return $this->Mail->add($post);
    }

    /**
     * Gets email
     * @param $mail_id
     * @return mixed
     */
    public function get($mail_id) {
        return $this->Mail->get(array('mail_id'=>$mail_id));
    }

    /**
     * Get all emails
     * @param null $status
     * @return mixed
     */
    public function all($status=null) {
        $id = (!is_null($status)) ? array('status'=>$status):array('status'=>0);
        $param = array('dir'=>'DESC', 'order'=>'date');
        return $this->Mail->all($id, $param);
    }

    /**
     * Update email
     * @param array $post
     * @param $id
     * @return bool
     */
    public function update(array $post, $id) {
        return $this->Mail->update($post, array('mail_id', $id));
    }

    /**
     * @param array $content
     * @param array $mailing_list
     * @return mixed
     */
    public function saveAndSend(array $content, array $mailing_list) {

        // Generate ID
        $data['mail_id'] = $this->Mail->generateID('mail_id');
        $data['date'] = date('Y-m-d h:i:s'); // Date of creation
        $body = $this->view->template($content['body'], $mailing_list[0], $data['mail_id']);

        // Format email content
        $data['content'] = $body;
        $data['subject'] = $content['subject'];
        $data['recipients'] = implode(',', $mailing_list);
        $data['attachments'] = !empty($content['attachments']) ? $content['attachments'] : null;

        if (!is_null($data['attachments'])) {
            $attachments = array();
            foreach (explode(',', $data['attachments']) as $file_name) {
                if (is_file(PATH_TO_APP . $file_name)) {
                    $attachments[] = PATH_TO_APP . $file_name;
                }
            }
        } else {
            $attachments = null;
        }

        // 1: Add email to the MailManager table
        if ($this->Mail->add($data)) {

            // 2: Send email
            if ($this->MailAPI->send_mail($mailing_list, $data['subject'], $data['content'], $attachments)) {

                // 3: Update MailManager table
                $result['status'] = $this->Mail->update(array('status'=>1), array('mail_id'=>$data['mail_id']));
            } else {
                $result['status'] = false;
            }

        } else {
            $result['status'] = false;
        };

        if ($result['status']) {
            $result['msg'] = "Your message has been sent!";
        }
        return $result;

    }

    /**
     * Renders email content
     * @param $mail_id
     */
    public function show($mail_id) {
        $data = $this->get($mail_id);
        $attachments = (!empty($data->attachments)) ? explode(',', $data->attachments) : array();
        $content = $this->view->showEmail($data, $attachments);
        $this->render('mail.index', compact('content'));
    }

    /**
     * Clean email table
     * @param int|null $day
     * @return bool
     */
    public function clean($day=null) {
        return $this->Mail->deleteOldest($day);
    }
}