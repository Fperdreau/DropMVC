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

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AppController {

    function __construct() {
        parent::__construct();
        $this->loadModel('Publications');
        $this->loadModel('Tools');
    }

    public function index() {
        $publicationsController = new PublicationsController();
        $publications = $publicationsController->showLast(1);
        $toolsController = new ToolsController();
        $tools = $toolsController->showLast();
        $message = "    <span style='font-size: 1.7em; line-height: 2em; font-weight: 500; margin-bottom: 30px;'>HELLO & WELCOME!</span>
            <p style='font-style: italic; font-size: 1.2em; line-height: 1.3em; margin-bottom: 30px;'>
            I am a postdoctoral researcher in Cognitive Neurosciences formerly trained in Philosophy
            and Cognitive Psychology. Here you may find further information regarding my academic background, my publications,
            research tools I developed, and my contact information.</p>
            <p style='font-size: 1.4em; font-weight: 500;'>I wish you a good visit!</p>
            <p style='margin-top: 50px; font-size: 1.2em; font-style: oblique; font-weight: 300;'>Florian Perdreau</p>";
        $this->render('home.index', compact('publications', 'tools', 'message'));
    }
}