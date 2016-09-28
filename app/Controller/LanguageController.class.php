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


use App\App;
use Core\Language\Translation;

/**
 * Class LanguageController
 * @package App\Controller
 */
class LanguageController extends AppController {

    public function set($lang) {
        $translation = new Translation();
        $translation->set($lang);
        $new_url = str_replace($_SESSION['BASE_URL'], URL_TO_APP . $lang.'/', $_SESSION['current_url']);
        App::getAppUrl($lang);
        $_SESSION['lang'] = $lang;
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
           header('Location: ' . $new_url);
        } else {
            echo  json_encode($new_url);
        }
    }

}