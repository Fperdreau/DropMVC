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


use App\App;

/**
 * Class AppView
 * @package App\Views
 */
class AppView {

    /**
     * @param \Core\HTML\Form $form
     * @return string
     */
    public static function settings($form) {
        $formContent = "<div class='submit_btns'>" . $form->submit('processform') . "</div>";
        $formContent .= $form->select('status','Application status', array('On'=>1,'Off'=>0));
        $formContent .= $form->input('siteTitle','WebSite title', array('type'=>'text'));
        $formContent .= $form->input('siteSubTitle','WebSite subtitle', array('type'=>'text'));
        $formContent .= $form->select('template','Template', App::getTemplates());
        return $formContent;
    }

}