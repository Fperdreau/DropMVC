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

$modal = "

<div id='modal' class='modalContainer' style='display:none;'>

    <div class='popupBody'>

        <!-- Sign in section -->
        <div class='modal_section' id='user_login' data-title='Sign In'>
            ".\App\Views\AuthView::login()."
        </div>

        <!-- Change password section -->
        <div class='modal_section' id='user_changepw' data-title='Change Password'>
            ".\App\Views\AuthView::forgotPassword()."
        </div>

        <!-- Delete submission (confirmation) section -->
        <div class='modal_section' id='delete_confirmation' data-title='Delete'>
            <div>Do you want to delete this item?</div>
            <div class='action_btns'>
                <div class='one_half'><a href='' class='btn close_modal'><i class='fa fa-angle-double-left'></i> Cancel</a></div>
                <div class='one_half last'><a href='' class='btn btn_red confirm_delete'>Delete</a></div>
            </div>
        </div>

        <!-- Submission form section -->
        <div class='modal_section' id='form' data-title='Add/Edit'></div>
        <div class='modal_section' id='terms' data-title='Terms and Conditions'></div>
        <div class='modal_section' id='photo' data-title='photo'></div>        
        <div class='modal_section' id='submission_form' data-title='Presentation'></div>
        <div class='modal_section' id='cv_form' data-title='CV'></div>
        <div class='modal_section' id='tools' data-title='Tools'></div>
        <div class='feedback'></div>
        <div class='modal_close_btn close_modal'></div>

    </div>
</div>";


