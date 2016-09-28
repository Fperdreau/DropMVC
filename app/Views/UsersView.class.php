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

namespace App\Views;


use App\Entity\UsersEntity;
use Core\HTML\BootstrapForm;
use Core\Language\Translation;

/**
 * Views for user profile
 * Class UsersView
 * @package App\Views
 */
class UsersView {

    /**
     * Render user information
     * @param BootstrapForm $form
     * @param UsersEntity $user
     * @return string
     */
    public static function information(BootstrapForm $form, UsersEntity $user) {
        $action = URL_TO_APP ."users/update/{$user->username}";
        $formURL = URL_TO_APP . 'Users/information/'.$user->username;
        $languages = array();
        foreach (Translation::getLanguages() as $langCode=>$langName) {
            $languages[$langName] = $langCode;
        }
        $selectLang = $form->select('lang','Language', $languages, "required data-url='{$formURL}'", "select_lang");

        $username = $form->input('username','Username',array('type'=>'text'));
        $firstname = $form->input('firstname','First name',array('type'=>'text'));
        $lastname = $form->input('lastname','Last name',array('type'=>'text'));
        $title = $form->input('title','Academic title',array('type'=>'text'));
        $position = $form->input('position','Current position',array('type'=>'text'));
        $birthday = $form->input('birthday','Date of birth',array('type'=>'date'));
        $nationality = $form->input('nationality','Nationality',array('type'=>'text'));
        $description = $form->input('description','Description', array('type'=>'textarea', 'class'=>'tinymce', 'id'=>'description'));
        $submit = $form->submit('processform');

        return "
        <section id='personal_form'>
            <h1>Personal information</h1>
                <div class='trad_form_container' id='profile_info'>
                    <form method='post' action='{$action}'>
                        <div class='submit_btns'>
                            $submit
                        </div>
                        $selectLang
                        $username
                        $firstname
                        $lastname
                        {$birthday}
                        {$nationality}
                        {$title}
                        {$position}
                        {$description}
                    </form>
                </div>
        </section>
        ";
    }

    /**
     * Login information form
     * @param UsersEntity $user
     * @return string
     */
    public static function login(UsersEntity $user) {
        return "
            <section id='login_form'>
                <h1>Login information</h1>
                <form method='post' action='" . URL_TO_APP . 'users/modify_login/' . $user->username . "'>
                    <div class='form-group'>
                        <label>Username</label>
                        <input type='text' name='username' value='{$user->username}'>
                    </div>
                    <div class='form-group'>
                        <label>Password</label>
                        <input type='password' name='password' value='' required autocomplete='on'>
                    </div>
                    <div class='form-group'>
                        <label>New password</label>
                        <input type='password' name='new_password' class='passwordChecker' value=''>
                    </div>
                    <div class='form-group'>
                        <label>Confirm new password</label>
                        <input type='password' name='conf_new_password' value=''>
                    </div>
                    <div class='submit_btns'>
                        <button type='submit' class='modify_login'>Modify</button>
                    </div>
                </form>
            </section>
        ";
    }

    /**
     * @param BootstrapForm $form
     * @param UsersEntity $user
     * @return string
     */
    public static function contact(BootstrapForm $form, UsersEntity $user) {
        $action = URL_TO_APP ."users/update/{$user->username}";
        $submit = $form->submit('processform');

        $university = $form->input('university','University/Institution',array('type'=>'text'));
        $department = $form->input('department','Department',array('type'=>'text'));
        $office = $form->input('office','Office',array('type'=>'text'));
        $email = $form->input('email','Email',array('type'=>'email'));
        $address = $form->input('address','Postal address',array('type'=>'text'));
        $postcode = $form->input('postcode','Post code',array('type'=>'text'));
        $city = $form->input('city','City',array('type'=>'text'));
        $country = $form->input('country','Country',array('type'=>'text'));
        $map = $form->input('mapurl','Google MAP url',array('type'=>'text'));

        return "
        <section id='contact_form'>
            <h1>Contact information</h1>
            <form method='post' action='{$action}'>
                <div class='submit_btns'>
                    $submit
                </div>
                $university
                $department
                $email
                $address
                $postcode
                $city
                $country
                $map
                $office
            </form>
        </section>

        ";
    }

    /**
     * Show profile picture
     * @param UsersEntity $user
     * @return string
     */
    public static function photoEdit(UsersEntity $user) {
        $modify_photo = URL_TO_APP . "users/modify_photo/".$user->username;
        return "
             <section>
                <h1>My Picture</h1>
                <div id='profile_photo'>
                    <img src='{$user->photo}'>
                    <a href='{$modify_photo}' class='leanModal' data-section='form'>
                        <div id='profile_photo_modify'>
                            <div id='icon'></div>
                            <div>Change my picture</div>
                        </div>
                    </a>
                </div>
            </section>
        ";
    }

    /**
     * Display user creation form
     * @param BootstrapForm $form
     * @param string $op
     * @param string $username
     * @return string
     */
    public function form(BootstrapForm $form, $op='add', $username='') {
        $action = ($op == 'add') ? URL_TO_APP ."users/add/":URL_TO_APP ."users/update/".$username;
        $submit = $form->submit('processform', 'Next');
        $formContent = $form->input('username','Username',array('type'=>"text"), "required autocomplete='on'");
        $formContent .= $form->input('password','Password',array('type'=>"password",'class'=>'passwordChecker'), "required autocomplete='on'");
        $formContent .= $form->input('conf_password','Confirm Password',array('type'=>"password"), "required autocomplete='on'");
        $formContent .= $form->input('email','Email',array('type'=>"email"), "required autocomplete='on'");
        return "
            <form id='admin_creation' method='post' action='{$action}'>
                <input type='hidden' name='status' value='admin'/>
                {$formContent}
                <div class='submit_btns'>
                    $submit
                </div>
			</form>
        ";
    }

    /**
     * Profile photo form
     * @param $uploader
     * @param $username
     * @return string
     */
    public static function photo_form($uploader, $username) {
        $url = URL_TO_APP . "users/edit_photo/" . $username;
        return "
        <div id='photo_form'>
            <form method='post' action='{$url}' id='submit_form'>
                <div class='submit_btns'>
                    <input type='submit' class='processform close_modal' value='Modify my picture' />
                </div>
            </form>
            {$uploader}
        </div>

        ";
    }

    /**
     * Password modification form
     * @param BootstrapForm $form
     * @param $username
     * @return string
     */
    public static function modify_password(BootstrapForm $form, $username) {
        $action = URL_TO_APP . "users/update/".$username;
        $formContent = $form->input('password', 'Password', array('type'=>'password'), 'required');
        $formContent .= $form->input('conf_password', 'Confirm password', array('type'=>'password'), 'required');

        return "
        <form method='post' action='{$action}'>
            {$formContent}
            <div class='submit_btns'>
                <button type='submit' class='processform'>Modify</button>
            </div>
        </form>
        ";
    }

}