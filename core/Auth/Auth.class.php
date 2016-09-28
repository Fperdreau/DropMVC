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

namespace Core\Auth;

use App\App;
use App\Entity\UsersEntity;
use Core\Controllers\BaseController;
use Core\Settings;
use DateTime;

require_once('PasswordHash.php');

/**
 * Class Auth
 * @package Core\Auth
 */
class Auth extends BaseController{

    /**
     * Settings
     * @var array
     */
    private $settings = array('max_nb_attempt'=>5);

    /**
     * @var Settings
     */
    private $config;

    /**
     * @var \App\Model\UsersModel
     */
    protected $Users;

    /**
     * Auth constructor.
     */
    function __construct(){
        parent::__construct();
        $this->loadModel('Users');
        $this->config = App::getInstance()->loadSettings('Auth', $this->settings);
    }

    /**
     * Get id of logged user
     * @return bool
     */
    public static function getUserId(){
        if(self::logged()){
            return $_SESSION['auth'];
        }
        return false;
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public static function logged(){
        return isset($_SESSION['auth']);
    }

    /**
     * Login routine
     * @return mixed
     */
    public function login() {
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $user = $this->Users->get(array('username'=>$username));
        if (!empty($user->username)) {
            if ($user->active == 1) {
                if ($this -> check_pwd($username, $password) == true) {
                    $_SESSION['auth']  = $user->username;
                    $_SESSION['username'] = $user->username;
                    $_SESSION['status'] = $user -> status;
                    $result['msg'] = "Hi $user->firstname,<br> welcome back!";
                    $result['status'] = true;
                } else {
                    $result['status'] = false;
                    $attempt = $this->checkAttempt($user);
                    if ($attempt == false) {
                        $result['msg'] = "Wrong password. You have exceeded the maximum number
                            of possible attempts, hence your account has been deactivated for security reasons.
                            We have sent an email to your address ({$user->email}) including an activation link.";
                    } else {
                        $result['msg'] = "Wrong password. $attempt login attempts remaining";
                    }
                }
            } else {
                $result['status'] = false;
                $result['msg'] = "Sorry, your account is not activated yet. <br> You will receive an
                    email as soon as your registration is confirmed by an admin.<br> Please,
                    <a href='".URL_TO_APP . 'contact'."'>contact us</a> if you have any question.";
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "Wrong username";
        }
        return $result;
    }

    /**
     * Check number of unsuccessful login attempts.
     * Deactivate the user's account if this number exceeds the maximum
     * allowed number of attempts and send an email to the user with an activation link.
     * @param UsersEntity $user
     * @return int
     */
    private function checkAttempt(UsersEntity $user) {
        $last_login = new DateTime($user->last_login);
        $now = new DateTime();
        $diff = $now->diff($last_login);

        // Reset the number of attempts if last login attempt was 1 hour ago
        $user->attempt = $diff->h >= 1 ? 0:$user->attempt;
        $user->attempt += 1;
        if ($user->attempt >= (int)$this->config->settings['max_nb_attempt']) {
            $this->Users->activation($user->hash, $user->email, 0); // We deactivate the user's account
            $this->send_activation_mail();
            return false;
        }
        $user->last_login = date('Y-m-d H:i:s');
        $this->Users->update(array('attempt'=>$user->attempt,'last_login'=>$user->last_login),
            array('username'=>$user->username));
        return (int)$this->config->settings['max_nb_attempt'] - $user->attempt;
    }

    /**
     * This function sends an email to the user to activate his/her account
     */
    private function send_activation_mail() {

    }

    /**
     * Check if the provided password is correct (TRUE) or not (FALSE)
     *
     * @param string $username
     * @param string $password : password provided by the user
     * @return bool
     */
    function check_pwd($username, $password) {
        $user = $this->Users->get(array('username'=>$username));
        $check = validate_password($password, $user->password);
        if ($check == 1) {
            $user->attempt = 0;
            $user->last_login = date('Y-m-d H:i:s');
            if ($this->Users->update(array('attempt'=>$user->attempt,'last_login'=>$user->last_login), array('username'=>$username))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}