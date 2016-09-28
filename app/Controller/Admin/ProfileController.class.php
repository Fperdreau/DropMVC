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

namespace App\Controller\Admin;


use App\Controller\LinksController;
use App\Controller\SocialnetController;
use App\Controller\UsersController;
use App\Views\UsersView;
use Core\HTML\BootstrapForm;

/**
 * Class ProfileController
 * @package App\Controller\Admin
 */
class ProfileController extends UsersController {

    /**
     * @var UsersView
     */
    protected $view;
    /**
     * ProfileController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Users');
        $this->loadModel('Media');
        $this->loadModel('SocialNet');
        $this->loadView('Users');
    }

    /**
     * Render profile page
     */
    public function index() {
        $User = $this->getAdmin();
        $sn = new SocialnetController();
        $form = new BootstrapForm($User,'div');
        $Links = new LinksController();
        
        $content = $this->view->photoEdit($User);
        $content .= $this->view->login($User);
        $content .= $this->view->information($form, $User);
        $content .= $this->view->contact($form, $User);
        $content .= $sn->form($User->username);
        $content .= $Links->showForms();
        
        $this->render('admin.profile.index',compact('content'));;
    }

}
