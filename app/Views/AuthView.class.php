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


/**
 * Class AuthView
 * @package App\Views
 */
class AuthView {

    /**
     * Login form
     * @return string
     */
    public static function login() {
        return "
            <div class='container'>
            <section id='login_box'>
                <h1>Log In</h1>
                <form method='post' action='". URL_TO_APP ."auth/login/'>
                    <fieldset><input type='hidden' name='login' value='true'/></fieldset>
                    <fieldset class='form-group'>
                        <label>Username</label>
                        <input class='form-control' type='text' name='username' required autocomplete='on'/>
                    </fieldset>
                    <fieldset class='form-group'>
                        <label>Password</label>
                        <input class='form-control' type='password' name='password' required/>
                    </fieldset>
                    <fieldset class='submit_btns'>
                        <input type='submit' value='Log In' class='login'/>
                    </fieldset>
                </form>
                <div class='forgot_password leanModal' data-section='user_changepw' id='modal_trigger_changepw'>I forgot my password</div>
            </section>
            </div>
        ";
    }

    /**
     * Password request form
     * @return string
     */
    public static function forgotPassword() {
        $action = URL_TO_APP . 'users/forgot_password';
        return "
        <div class='modal_container'>
            <div class='modal_description'>We will send an email to the provided address with further instructions in order
             to change your password.</div>
            <form method='post' action='{$action}'>
                <fieldset class='form-group'>
                    <label>Email</label>
                    <input type='email' name='email' value='' required/>
                </fieldset>
                <div class='action_btns'>
                    <div class='one_half'><button type='submit' class='close_modal'>Cancel</button></div>
                    <div class='one_half last'><button type='submit' class='processform'>Send</button></div>
                </div>
            </form>
        </div>
        ";
    }

}