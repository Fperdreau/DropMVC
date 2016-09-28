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


use App\Model\PagesModel;
use App\Model\UsersModel;
use App\Views\MenuView;
use Core\Auth\Auth;
use Core\Controllers\BaseController;
use Core\Language\Translation;

/**
 * Class MenuController
 * @package App\Controller
 */
class MenuController extends BaseController {

    /**
     * @var
     */
    private static $instance;

    /**
     * Main menu
     * @var string
     */
    public $menu;

    /**
     * Sub menu HTML stored in associative array (key = parent's name)
     * @var array
     */
    public $submenu=array();

    /**
     * @var MenuView
     */
    protected $view;

    /**
     * @var PagesModel
     */
    protected $Pages;

    /**
     * @var UsersModel
     */
    protected $Users;

    /**
     * @var string|null
     */
    protected $current;

    /**
     * MenuController constructor.
     * @param null $current_page
     */
    protected function __construct($current_page) {
        parent::__construct();
        $this->current = $current_page;
        $this->loadModel('Pages');
        $this->loadModel('Users');
        $this->loadView('Menu');
        $this->build();
    }

    /** Menu factory
     * @param $current_page
     * @return MenuController
     */
    public static function getInstance($current_page) {
        if (is_null(self::$instance)) {
            self::$instance = new self($current_page);
        }
        return self::$instance;
    }

    /**
     * Build Menu
     * @return mixed
     */
    private function build() {
        if (is_null($this->menu)) {
            if (Auth::logged()) {
                $user = $this->Users->single(array('username'=>$_SESSION['username']));
                $status = ($user !== false) ? AppController::$accessLevels[$user->status] : -1;
            } else {
                $status = -1;
            }
            $all = array();
            foreach ($data = $this->Pages->getSections($status) as $key=>$page) {
                $all[] = $this->Pages->get(array('name'=>$page->name));
            }
            $menu = self::convertAdjacencyListToTree('', $all, 'name','parent','submenu');
            $this->menu = $this->view->menu($menu);
            $this->submenu = $this->view->submenu($menu, $this->current);
        }
    }

    /**
     * @param $main
     * @return bool
     */
    public function getSubMenu($main) {
        if (isset($this->submenu[$main])) {
            return $this->submenu[$main];
        }
        return false;
    }

    /**
     * Iteratively build menu tree
     * @param $intParentId
     * @param $arrRows
     * @param $strIdField
     * @param $strParentsIdField
     * @param $strNameResolution
     * @return array
     */
    private static function convertAdjacencyListToTree($intParentId,&$arrRows,$strIdField,$strParentsIdField,$strNameResolution) {

        $arrChildren = array();

        // Step 1: get main sections that match the current user's access rights
        $i = 0;
        foreach($arrRows as $element) {
            $access = AppController::checkAccess($element->status);
            if ($intParentId === $element->parent) {
                $arrChildren = array_merge($arrChildren, array_splice($arrRows,$i--,1));
            }
            $i++;
        }

        // Step 2: Get sub-sections
        $intChildren = count($arrChildren);
        if($intChildren != 0) {
            for($i=0;$i<$intChildren;$i++) {
                $arrChildren[$i]->$strNameResolution = self::convertAdjacencyListToTree($arrChildren[$i]->$strIdField,$arrRows,$strIdField,$strParentsIdField,$strNameResolution);

                $admin = ($intParentId === "admin") ? 'Admin\\' : null;
                $split = explode('/',$arrChildren[$i]->$strIdField);
                $name = end($split);

                // Check if the controller has a built-in menu
                $controllerName = "\\App\\Controller\\" . $admin . ucfirst($name) . "Controller";
                $controller = new $controllerName();

                // Get virtual menu if exists
                if (method_exists($controller, 'menu')) {
                    $arrChildren[$i]->$strNameResolution = array_merge($arrChildren[$i]->$strNameResolution,$controller->menu());
                }
            }
        }

        return $arrChildren;

    }

    /**
     * Gets language menu
     * @return string
     */
    public function getLanguageMenu() {
        return $this->view->languagesMenu(Translation::getLanguages());
    }

}