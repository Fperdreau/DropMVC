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
use App\Model\SitemapModel;
use App\Views\SitemapView;
use Sitemap;

require_once PATH_TO_LIBS . 'sitemap-php-master/Sitemap.php';

/**
 * Class SiteMapController
 * @package App\Controller
 */
class SitemapController extends AppController {

    /**
     * @var SitemapModel $SiteMap
     */
    protected $SiteMap;

    /**
     * @var PagesModel $Pages
     */
    protected $Pages;

    /**
     * @var SitemapView $view
     */
    protected $view;

    /**
     * @var Sitemap $sitemap
     */
    protected static $sitemap;

    /**
     * @var string $folder: absolute path to sitemap folder
     */
    protected static $folder;

    /**
     * @var string $filename: sitemap filename
     */
    protected static $filename = 'sitemap';

    protected static $path;


    /**
     * SiteMapController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Sitemap');
        $this->loadView('Sitemap');
        $this->loadModel('Pages');
        self::$folder = URL_TO_APP . 'sitemap/';
        self::$path = PATH_TO_APP . 'sitemap/';
        self::getSiteMap();
    }

    /**
     * Gets Sitemap instance
     * @return Sitemap
     */
    protected static function getSiteMap() {
        if (is_null(self::$sitemap)) {
            self::$sitemap = new Sitemap(URL_TO_APP);
            self::$sitemap->setPath(self::$path);
            self::$sitemap->setFilename(self::$filename);
        }
        return self::$sitemap;
    }

    /**
     * Generates sitemap
     */
    public function generate() {
        $this->clean();
        
        $sitemap = array();
        foreach ($this->build() as $item) {
            if ($item->show_menu === 0) continue;
            if (!empty($item->submenu)) {
                foreach ($item->submenu as $subitems) {
                    $sitemap[] = $subitems->name;
                    self::$sitemap->addItem($subitems->name);
                }
            } else {
                $sitemap[] = $item->name;
                self::$sitemap->addItem($item->name);

            }
        }
        self::$sitemap->createSitemapIndex(self::$folder, 'Today');

        $result['status'] = true;
        $result['content'] = htmlspecialchars($this->loadSiteMap());
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Loads sitemap file content
     *
     * @return null|string
     */
    public static function loadSiteMap() {
        $path = self::$path . self::$filename . '.xml';
        if (is_file($path)) {
            $logs = '';
            $fh = fopen($path,'r');
            while ($line = fgets($fh)) {
                $logs .= "{$line}";
            }
            fclose($fh);
        } else {
            $logs = null;
        }
        return $logs;
    }

    protected static function flatten($data) {
        $sitemap = array();
        foreach ($data as $item) {
            if ($item->show_menu === 0) continue;
            if (!empty($item->submenu)) {
                foreach ($item->submenu as $subitems) {
                    $sitemap[] = $subitems->name;
                }
            } else {
                $sitemap[] = $item->name;
            }
        }
        return $sitemap;
    }

    protected static function hierarchy($data) {
        $result = array();
        foreach ($data as $key=>$item) {
            if (!empty($item->submenu)) {
                $result[$item->name] = self::hierarchy($item->submenu);
            } else {
                $result[$item->name] = array();
            }
        }
        return $result;
    }

    /**
     * Display sitemap
     */
    public function index() {
        $content = $this->loadSiteMap();
        $this->render('sitemap.index', compact('content'));
    }

    /**
     * Build Menu
     * @return mixed
     */
    protected function build() {
        $status = -1;
        $all = array();
        foreach ($data = $this->Pages->getSections($status) as $key=>$page) {
            $page_data = $this->Pages->get(array('name'=>$page->name));
            if ($page_data->show_menu) {
                $all[] = $page_data;
            }
        }
        $menu = self::convertAdjacencyListToTree('', $all, 'name','parent','submenu');
        return $menu;
    }

    /**
     * Cleans sitemap directory
     */
    protected function clean() {
        if (!is_dir(self::$path)) mkdir(self::$path);

        foreach (scandir(self::$path) as $element) {
            $split = explode('.', $element);
            if (!in_array($element, array('.', '..')) && end($split) === 'xml') {
                unlink(self::$path.'/'.$element);
            }
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
    protected static function convertAdjacencyListToTree($intParentId,&$arrRows,$strIdField,$strParentsIdField,$strNameResolution) {

        $arrChildren = array();

        // Step 1: get main sections that match the current user's access rights
        $i = 0;
        foreach($arrRows as $element) {
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
                if (method_exists($controller, 'sitemap')) {
                    $arrChildren[$i]->$strNameResolution = array_merge($arrChildren[$i]->$strNameResolution,$controller->sitemap());
                }
            }
        }

        return $arrChildren;

    }


}