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


use App\Model\PolicyModel;
use App\Views\PolicyView;
use Core\HTML\BootstrapForm;
use Core\Language\Translation;

/**
 * Class PolicyController
 * @package App\Controller
 */
class PolicyController extends AppController {

    /**
     * @var PolicyModel
     */
    protected $Policy;

    /**
     * @var PolicyView
     */
    protected $view;

    /**
     * PolicyController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Policy');
        $this->loadView('Policy');
    }

    /**
     * Renders index page
     */
    public function index() {
        $data = $this->Policy->getAll('policyid');
        $content = $this->view->policies($data);
        $name = "";
        $this->render('policy.index',compact('content', 'name'), true);
    }

    /**
     * Renders policy page
     * @param $name
     */
    public function show($name) {
        $data = $this->Policy->getByName(array('clean_url'=>$name), 'policyid');
        $data = $this->Policy->get(array('policyid' => $data->policyid));
        $name = $data->name;
        $content = htmlspecialchars_decode($data->content);
        $this->render('policy.index',compact('content', 'name'), true);
    }

    /**
     * Set cookie consent
     */
    public function consent() {
        $_SESSION['cookies_consent'] = 'yes';
        return true;
    }

    public function policy_menu() {
        $this->loadView('Policy');
        $data = $this->Policy->getAll('policyid');
        return $this->view->policy_menu($data);
    }

    /**
     * Renders edit form
     * @param bool $id
     * @param null $lang
     */
    public function edit($id=null, $lang=null) {
        if (array_key_exists($id, Translation::getLanguages())) {
            $lang = $id;
            $id = null;
        }

        $this->loadView('Policy');
        $id = (isset($_POST['select_post'])) ? htmlspecialchars($_POST['select_post']) : $id;
        $id = ($id === 'add') ? null:$id;
        $lang = (isset($_POST['select_lang'])) ? htmlspecialchars($_POST['select_lang']) : $lang;

        $policy = (!is_null($id)) ? $this->Policy->get(array('policyid'=>$id), $lang):null;

        // Get publication info if requested
        $data = !is_null($policy) ? $policy : array();

        // Set language
        if (is_array($data)) {
            $data['lang'] = (is_null($lang)) ? $_SESSION['lang']:$lang;
        }

        $form = new BootstrapForm($data);
        $result = $this->view->form($form, $policy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            $this->render('admin.policy.index',compact('blogList','user'));
        }
    }

    /**
     * Update policy
     * @param $id
     * @return bool
     */
    public function update($id=null) {
        if (is_null($id) || empty($id)) {
            $_POST['policyid'] = $this->Policy->generateID('policyid');
            $_POST['clean_url'] = $this->clean_url($_POST['name']);
            $result['status'] = $this->Policy->add($_POST);
        } else {
            $_POST['clean_url'] = $this->clean_url($_POST['name']);
            $result['status'] = $this->Policy->update($_POST, array('policyid'=>$id), $_POST['lang']);
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            header('Location: '.URL_TO_APP.'admin/policy');
        }
        return true;
    }

    /**
     * Deletes policy from DB
     * @param $id
     * @return bool
     */
    public function delete($id) {
        $result['status'] = $this->Policy->delete(array('policyid'=>$id));
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            header('Location: '.URL_TO_APP.'admin/policy');
        }
        return true;
    }
}
