<?php
/**
 * File for class MailSender
 *
 * PHP version 5
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of Journal Club Manager.
 *
 * Journal Club Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Journal Club Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Cronjobs;

use App\Controller\MailController;
use App\Controller\TasksController;
use App\Model\MailModel;
use Core\Database\Database;
use Core\Mail\Mail;


/**
 * Class MailSender
 */
class MailSender extends TasksController {

    /**
     * @var array $default: Default settings
     */
    public $default = array(
        'name'=>'MailSender',
        'status'=>'Off',
        'options'=>array(
            'nb_version'=>array(
                'options'=>array(),
                'value'=>10)
        ),
        'time' => '1970-01-01 00:00:00',
        'frequency' => '0,0,0,0',
        'description' => "Checks that all emails have been sent and sends them otherwise. It also cleans the
    mailing database by deleting the oldest emails. The number of days of email storage can be defined in the task's 
    settings (default is 10 days)."
    );

    /**
     * @var Mail
     */
    private static $AppMail;

    /**
     * @var MailController
     */
    private $Manager;

    /**
     * @var MailModel $Mail
     */
    public $Mail;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->Manager = new MailController();
        $this->loadModel('Mail');
    }

    /**
     * Factory
     * @return Mail
     */
    private function getMailer() {
        if (is_null(self::$AppMail)) {
            self::$AppMail = new Mail();
        }
        return self::$AppMail;
    }

    /**
     * Sends emails
     * @return mixed
     */
    public function start() {
        // Clean DB
        $this->clean();

        // Get Mail API
        $this->getMailer();

        $sent = 0;
        foreach ($this->Manager->all(0) as $key=>$email) {
            $recipients = explode(',', $email->recipients);
            $attachment = (!empty($email->attachment)) ? $email->attachment : null;
            if (self::$AppMail->send_mail($recipients, $email->subject, htmlspecialchars_decode($email->content), $attachment)) {
                $res = $this->Mail->update(array('status'=>1), array('mail_id'=>$email->mail_id));
                $sent += 1;
            }
        }

        $result['msg'] = "{$sent} emails have been sent.";
        $result['status'] = true;
        return $result;
    }

    /**
     * Clean email table
     * @param int|null $day
     * @return bool
     */
    public function clean($day=null) {
        $task = $this->get($this->default['name']);
        $day = (is_null($day)) ? $task->options['nb_version']['value']: $day;
        return $this->Manager->clean($day);
    }
}