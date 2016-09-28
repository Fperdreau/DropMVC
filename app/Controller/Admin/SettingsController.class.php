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


use App\Model\SettingsModel;
use App\Views\SettingsView;
use Core\HTML\BootstrapForm;

/**
 * Class SettingsController
 * @package App\Controller\Admin
 */
class SettingsController extends \App\Controller\SettingsController {

    /**
     * @var SettingsModel
     */
    protected $Settings;

    /**
     * @var SettingsView
     */
    protected $view;

    /**
     * SettingsController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Settings');
        $this->loadView('Settings');
    }

    /**
     * Renders settings page (admin)
     */
    public function index() {
        $App = $this->show('App');
        $Mail = $this->show('Mail');
        $Media = $this->show('Media');
        $this->render('admin.settings.index',compact('App', 'Mail', 'Media'));
    }

    /**
     * Show settings form
     * @param string $name
     * @return string
     */
    public function show($name) {
        $settings = $this->Settings->get(array('object'=>$name));
        $form = new BootstrapForm($settings, 'div');
        $this->loadView($name);
        $formcontent = $this->view->settings($form);
        $this->loadView('Settings');
        return $this->view->show($formcontent, $name);
    }

}