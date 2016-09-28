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
use App\Model\PagesModel;
use App\Views\PagesView;
use Core\HTML\BootstrapForm;

/**
 * Class PagesController
 *
 * This class handles pages settings, information and description, as well as their rendering.
 */
class PagesController extends AppController {

    protected $pathToPages;

    /**
     * @var PagesView
     */
    protected $view;

    /**
     * @var PagesModel
     */
    protected $Pages;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->pathToPages = PATH_TO_APP . DS . 'app' . DS . 'Views' . DS;
        $this->loadModel('Pages');
        $this->loadView('Pages');
        $this->template = $this->Pages->settings['template'];

        if(!defined('PATH_TO_CSS')) define('PATH_TO_CSS', URL_TO_APP . 'app/Views/templates/' . $this->template . '/css/');
    }

    /**
     * Show pages list (admin)
     */
    public function index() {
        $this->loadView('Pages');
        $all = $this->Pages->all();
        $pages = '';
        foreach ($all as $page) {
            $form = new BootstrapForm($page, 'div');
            $pages .= $this->view->show($form, $page, count($all));
        }
        $this->render('admin.pages.index', compact('pages'));
    }

    /**
     * Update page information
     * @param $id
     */
    public function update($id) {
        $result['status'] = $this->Pages->update($_POST, array('name'=>$id), $_POST['lang']);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            header('Location: '.URL_TO_APP.'admin/pages');
        }
    }

    /**
     * Delete page from db
     * @param $id
     */
    public function delete($id) {
        $result['status'] = $this->Pages->delete(array('name'=>$id));
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        } else {
            header('Location: '.URL_TO_APP.'admin/pages');
        }
    }

    /**
     * Get application pages
     * @return array
     */
    public function getPages() {
        // First cleanup Page table
        $this->cleanup();
        // Second, install new pages if there are any
        $folder = PATH_TO_APP . 'app' . DS . 'Views';
        $pages = $this->browse($folder, null, array('mail', 'error', 'templates', 'uploads'));
        return $pages;
    }

    /**
     * Browse the View directory
     * @param $dir
     * @param null $parent
     * @param array $excludes
     * @return array
     */
    private function browse($dir, $parent=null, array $excludes=array()) {
        $content = scandir($dir);
        $temp_dir = array();
        $rank = 0;
        foreach ($content as $element) {
            if ($element !== "." && $element !== ".." && is_dir($dir.DS.$element) && !in_array($element, $excludes)) {
                // Register page into the database if it is not
                $page_name = (is_null($parent)) ? $element: $parent.'/'.$element;
                $url = (is_null($parent)) ? URL_TO_APP . $element:URL_TO_APP . $parent .'/'. $element;
                if (!$this->Pages->is_exist(array('name'=>$page_name))) {
                    $status = ($element == "admin" || $parent == "admin") ? 2 : -1; // Restrict access by default for admin pages
                    $name = (!is_null($parent)) ? $parent.'/'.$element:$element;
                    $this->Pages->add(array(
                        'name'=>$name,
                        'filename'=>$url,
                        'status'=>$status,
                        'parent'=>$parent,
                        'show_menu'=>1,
                        'rank'=>$rank));
                }
                $temp_dir[$element] = $this->browse($dir.DS.$element, $element, $excludes);
            }
            ++$rank;
        }
        return $temp_dir;
    }

    /**
     * Clean up Pages table. Remove pages from the DB if they are not present in the Views folder
     */
    public function cleanup() {
        $folder = PATH_TO_APP . 'app' . DS . 'Views' . DS;
        foreach ($this->Pages->getInstalledPages() as $page) {
            $path = $folder . strtolower($page) .DS;
            if (!is_dir($path)) {
                $this->Pages->delete(array('name'=>$page));
            }
        }
    }

    /**
     * Renders edit form
     * @param $id
     * @param null $lang
     * @param bool $render
     * @return string
     */
    public function edit($id, $lang=null, $render=true) {
        $page = $this->Pages->get(array('name'=>$id), $lang);
        if ($page->parent !== "admin" && $page->name !== "admin") {
            $form = new BootstrapForm($page, 'div');
            $result = $this->view->show($form, $page, count($this->Pages->all()));
        } else {
            $result = "";
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if ($render) echo json_encode($result);
        }
        return $result;

    }

    /**
     * Get page information
     * @param $name
     * @return bool|mixed
     */
    public function get($name) {
        $result = $this->Pages->get(array('name'=>$name));
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return $result;
        } else {
            return $result;
        }
    }

}
