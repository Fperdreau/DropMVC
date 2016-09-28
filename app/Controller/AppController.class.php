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
use App\Views\AuthView;
use App\Views\PolicyView;
use Core\Auth\Auth;
use Core\Controllers\BaseController;
use Core\Language\Translation;
use Core\Session\SessionManager;


/**
 * Class AppController
 * @package App\Controller
 */
class AppController extends BaseController {

    /**
     * @var array: minimum level required to access the page
     */
    public static $accessLevels = array('none'=>-1,'admin'=>2);

    /**
     * @var bool: is the user allowed to access this page
     */
    protected $allowed = true;

    /**
     * @var $page PagesEntity
     */
    public $page;

    /**
     * @var array: main menu
     */
    public $menu;

    /**
     * @var array: submenu sections
     */
    public $submenu;

    /**
     * @var
     */
    public $languagesMenu;

    /**
     * AppController constructor.
     */
    function __construct() {
        parent::__construct($this->loadTemplate());
    }

    /**
     * Load html template
     * @return mixed
     */
    private function loadTemplate() {
        return App::getInstance()->getSettings('template');
    }

    /**
     * Set debug mode (display PHP errors)
     * @param bool $debug
     */
    public function debug($debug=false) {
        $_SESSION['debug'] = $debug;
    }

    /**
     * Get page information
     * @param array $page_info
     * @return bool|mixed
     */
    protected function loadPage(array $page_info=null) {
        $admin = (strpos(strtolower(get_class($this)), 'admin') > 0) ? 'Admin/':null;

        $ex = explode('\\',get_class($this));
        $pageName = end($ex);
        $pageName = str_replace('Controller','',$pageName);
        $Page = new PagesController();

        $this->page = $Page->get($admin.$pageName);
        $dft_page_name = (!empty($this->page->meta_title)) ? $this->page->meta_title : ucfirst(str_replace('/', ' - ', $pageName));

        if (!is_null($page_info)) {
            foreach ($page_info as $var=>$value) {
                if ($var !== "name") {
                    $this->page->$var = $value;
                }
            }
        }

        $meta_title = (!is_null($page_info) && isset($page_info['meta_title'])) ? $page_info['meta_title'] : null;
        $sep = (is_null($meta_title)) ? null : ' | ';
        $this->page->meta_title = $meta_title . $sep . $dft_page_name . ' | ' . App::getInstance()->getSettings('siteTitle');

        return $this->page;
    }

    /**
     * Build menus
     */
    protected function buildMenu() {
        $main = (!empty($this->page->parent)) ? $this->page->parent : $this->page->name;
        $Menu = MenuController::getInstance($main);
        $this->menu = $Menu->menu;

        $this->submenu = "";
        foreach ($Menu->submenu as $name=>$sections) {
            $this->submenu .= $sections;
        }
        $this->languagesMenu = $Menu->getLanguageMenu();
    }

    /**
     * Render social networks menu
     * @return string
     */
    private function getSocialNet() {
        $sn = new SocialnetController();
        return $sn->showAll();
    }

    /**
     * Renders policies menu
     * @return string
     */
    private function getPolicies() {
        $policies = new PolicyController();
        return $policies->policy_menu();
    }

    /**
     * Convert id to pretty url or the contrary
     * @param string $id
     * @param string $direction
     * @return string
     */
    public static function formatTitleUrl($id, $direction='url') {
        if ($direction == 'url') {
            $id = preg_replace("/[^A-Za-z0-9 ]/",'-',$id);
            return str_replace(" ", "-", $id);
        } else {
            return preg_replace('/-/',' ',$id);
        }
    }

    /**
     * Makes pretty URL
     * @param $title
     * @return mixed
     */
    public static function clean_url($title) {
        $title = str_replace(" ","-",strtolower($title));
        $title = str_replace('"','',strtolower($title));
        $title = str_replace("'","",strtolower($title));
        $title = str_replace(" & ","-",strtolower($title));
        $title = str_replace("&","-",strtolower($title));
        $title = str_replace(" ? ","-",strtolower($title));
        $title = str_replace("?","-",strtolower($title));
        $title = str_replace(":","",strtolower($title));
        $title = str_replace(",","",strtolower($title));
        $title = rtrim($title, '-');
      return $title;
    }

    /**
     * Build navigation bar
     * @return string
     */
    public static function navBar() {
        $split = explode('/',$_GET['url']);
        $name = $split[0];
        $baseURL = URL_TO_APP;
        $pageURL = URL_TO_APP . 'home';
        $content = "<div class='navBar_page' id='home'><a href='{$pageURL}'>Home</a></div>";
        foreach ($split as $page) {
            $baseURL .= '/'. $page;
            $content .= "<div class='navBar_arrow'>></div><div class='navBar_page'><a href='{$baseURL}'>{$page}</a></div>";
        }
        $result = ($name !== 'home') ? "<div class='wrap container'><div id='navBar_content'>{$content}</div></div>":"";
        return $result;
    }

    /**
     * Render page
     * @param string $view
     * @param array $variables
     * @param bool $force
     * @param array $page_info
     * @return bool|void
     */
    public function render($view, $variables=[], $force=false, array $page_info=null) {
        $_SESSION['current_url'] = URL_TO_APP . $_GET['url'];

        // Get page information (meta-data, access requirements)
        $page = $this->loadPage($page_info);

        // Check access permission
        $allowed = self::checkAccess($page->status);

        // Application status
        $AppStatus = App::getInstance()->getSettings('status');
        
        // Cookie consent bar
        //$cookie_consent = !isset($_SESSION['cookies_consent']) ? PolicyView::consent_bar():"";
        $cookie_consent = null;
        
        // HrefLang tags
        $hreflang = null;

        $siteTitle = App::getInstance()->getSettings('siteTitle');
        $siteSubTitle = App::getInstance()->getSettings('siteSubTitle');

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            // Build Menu
            $this->buildMenu();
            $navBar = "";

            // Load social networks
            $socialNet = $this->getSocialNet();

            // Load policies
            //$policies = $this->getPolicies();
            $policies = null;

            // Logout button (for admin only)
            $logout = ($allowed['user_status'] === 'admin') ? "<a href='".URL_TO_APP.'auth/logout'."' class='logout'>Logout</a>":"";
        }

        // Start buffering
        ob_start("ob_gzhandler");

        if (!empty($variables)) {
            extract($variables);
        }

        if ($force || $AppStatus == 1 || $page->parent === 'admin' || $allowed['user_status'] === 'admin' ) {
            if ($allowed['status'] || $force) {
                $view = str_replace('.', DS, $view);
                require($this->viewPath . $view . '.php');
            } else {
                if ($allowed['user_status'] === 'not_allowed') {
                    $this->forbidden();
                } else {
                    echo AuthView::login();
                    $allowed['status'] = true;
                }
            }
        } else {
            require($this->viewPath . 'error/work.php');
        }

        // End of buffering
        $pagecontent = ob_get_clean();
        $templatePath = $this->viewPath . 'templates' . DS . $this->template . DS . 'template.php';

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if (is_file($templatePath)) {
                require($templatePath);
            } else {
                require $this->viewPath . 'templates' . DS . 'default' . DS . 'template.php';
            }
        } else {
            $result['content'] = $pagecontent;
            $result['info'] = $page;
            $result['status'] = $allowed;
            //$result['submenu'] = $this->submenu;
            //$result['navBar'] = $navBar;
            echo json_encode($result);
        }
        return true;
    }

    /**
     * Check whether the user is logged in and has permissions to access the page
     * @param $pageStatus: page's access level
     * @return bool
     */
    public static function checkAccess($pageStatus) {
        $user = new UsersController();

        if (!Auth::logged()) {
            $result['user_status'] = false;
            $result['status'] = $pageStatus <= -1;
        } else {
            if (!isset($_SESSION['username'])) {
                $result['status'] = false;
                $result['user_status'] = false;
            } else {
                $user = $user->get($_SESSION['username']);
                $result['user_status'] = $user->status;
                if ( (!is_null(($user->status)) && self::$accessLevels[$user->status]>=$pageStatus) || $pageStatus == -1) {
                    $result['status'] = true;
                    $result['msg'] = $user->status;
                } else {
                    $result['status'] = false;
                    $result['user_status'] = 'not_allowed';
                }
            }
        }
        return $result;
    }

}
