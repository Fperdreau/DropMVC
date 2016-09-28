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

namespace App\Controller\Admin;

/**
 * Class PagesController
 *
 * This class handles pages settings, information and description, as well as their rendering.
 */
class PagesController extends \App\Controller\PagesController {

    public function index() {
        $all = $this->Pages->all();
        $pages = '';
        foreach ($all as $page) {
            $pages .= $this->edit($page->name, null, false);
        }
        $this->render('admin.pages.index', compact('pages'));
    }

}