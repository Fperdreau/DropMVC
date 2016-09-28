<?php
/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of DropMVC.
 *
 * DropMVC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropMVC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DropMVC.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Controller;

use App\Entity\MediaEntity;
use App\Entity\UsersEntity;
use App\Model\MediaModel;
use App\Model\UsersModel;
use App\Views\UsersView;
use Core\HTML\BootstrapForm;
use Core\Language\Translation;

/**
 * Class UsersController
 * @package App\Controller
 */
class UsersController extends AppController {

    /**
     * @var UsersModel
     */
    protected $Users;

    /**
     * @var MediaModel
     */
    protected $Media;

    /**
     * @var UsersView
     */
    protected $view;

    /**
     * UsersController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Users');
        $this->loadView('Users');
    }

    /**
     * Render user information form (AJAX only)
     * @param $id
     * @param null $lang
     */
    public function information($id, $lang=null) {
        $user = $this->Users->get(array('username'=>$id), $lang);
        $form = new BootstrapForm($user,'div');
        $this->loadView('Users');
        $result = $this->view->information($form, $user);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
    }

    /**
     * Update user information
     * @param $username
     */
    public function update($username) {
        if (isset($_POST['password'])) {
            $_POST['password'] = create_hash($_POST['password']);
        }
        $lang = (isset($_POST['lang'])) ? $_POST['lang']:Translation::$default_lang;
        $result['status'] = $this->Users->update($_POST, array('username'=>$username), $lang);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            echo $result['status'];
        }
    }

    /**
     * Modify login information
     * @param $username
     */
    public function modify_login($username) {
        $post = self::sanitize($_POST, false);
        $user = $this->Users->get(array('username'=>$username));
        if (validate_password($post['password'], $user->password)) {
            if (!empty($post['new_password'])) {
                if (!empty($post['conf_new_password'])) {
                    if ($post['new_password'] === $post['conf_new_password']) {
                        $result['status'] = $this->Users->update(array(
                            'username'=>$post['username'],
                            'password'=>create_hash($post['new_password'])),
                            array('username'=>$username));
                    } else {
                        $result['status'] = false;
                        $result['msg'] = "Passwords must match";
                    }
                } else {
                    $result['status'] = false;
                    $result['msg'] = 'You must confirm your new password';
                }
            } else {
                $result['status'] = $this->Users->update(array('username'=>$post['username']), array('username'=>$username));
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "Wrong password";
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            echo $result['status'];
        }
    }

    public function forgot_password() {
        $email = htmlspecialchars($_POST['email']);
        $user = $this->Users->single(array('email'=>$email));
        if ($user !== false) {
            $Mail = new MailController();
            $result['status'] = $Mail->send_password_request($user);
        } else {
            $result['status'] = false;
            $result['msg'] = "This email does not exist";
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            echo $result['status'];
        }
    }

    public function renew_pwd($hash, $email) {
        $user = $this->Users->single(array('hash'=>$hash, 'email'=>$email));
        if ($user !== false) {
            $form = new BootstrapForm();
            $content = $this->view->modify_password($form, $user->username);
        } else {
            $content = "<div class='msg_warning'>Incorrect email or hash id.</div>";
        }
        $content = "
            <section>
                <h2>Change password</h2>
                $content
            </section>";
        $this->render('admin.index', compact('content'), true);
    }

    public function verify($email, $hash) {

    }

    /**
     * Add user
     */
    public function add() {
        $result = $this->Users->make($_POST);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            echo $result['status'];
        }
    }

    /**
     * Get admin information
     * @return mixed
     */
    public function getAdmin() {
        $admin = $this->Users->getAdmin();
        if ($admin !== false) {
            return $this->get($admin->username);
        } else {
            return false;
        }
    }

    /**
     * Get user information
     * @param string $id: username
     * @return mixed
     */
    public function get($id) {
        $user = $this->Users->get(array('username'=>$id));
        $user = $this->getPhoto($user);
        return $user;
    }

    /**
     * Get profile picture
     * @param UsersEntity $user
     * @return UsersEntity
     */
    public function getPhoto(UsersEntity $user) {
        if (!empty($user->photo)) {
            $this->loadModel('Media');
            /**
             * @var MediaEntity $data
             */
            $data = $this->Media->get(array('fileid'=>$user->photo));
            if ($data === false) {
                $this->Users->update(array('photo'=>null), array('username'=>$user->username));
            }
            $user->photo = ($data !== false) ? URL_TO_UPLOADS . $data->filename:PATH_TO_IMG . "default_profile.jpg";
        } else {
            $user->photo = PATH_TO_IMG . "default_profile.jpg";
        }
        return $user;
    }

    /**
     * Render Modify profile picture form
     * @param $username
     */
    public function modify_photo($username) {
        $user = $this->Users->get(array('username'=>$username));
        $media = new MediaController();
        $links = (!is_null($user->photo)) ? array($user->photo=>$media->get($user->photo)):null;
        $uploader = $media->uploader($links);
        $user = $this->view->photo_form($uploader, $user->username);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($user);
        } else {
            $this->render('admin.profile.index', compact('user'));
        }
    }

    /**
     * Edit (add/modify) user's profile picture
     * @param $username
     */
    public function edit_photo($username) {
        $result['status'] = true;
        if (isset($_POST['upl_link'])) {
            $result['status'] = $this->Users->update(array('photo'=>$_POST['upl_link']), array('username'=>$username));
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            header('Location: '.URL_TO_APP.'admin/profile');
        }
    }

    /**
     * Render user creation form
     * @return string
     */
    public function showForm() {
        $data = $this->getAdmin();
        $user = ($data !== false) ? $data:array();
        $username = (!empty($user)) ? $user->username:"";
        $op = ($data !== false) ? 'update':'add';
        $form = new BootstrapForm($user,'div');
        $this->loadView('Users');
        return $this->view->form($form, $op, $username);
    }

}
