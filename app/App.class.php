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

namespace App;

use App\Controller\SettingsController;
use Core\Backup;
use Core\Database\Database;
use Core\Database\MySqlDb;
use Core\Models\BaseModel;
use Core\Settings;
use Core\Session\SessionManager;

/**
 * Class App
 * Singleton class
 * @package App
 */
class App {

    /**
     * Application instance
     * @var App|null
     */
    private static $instance;

    /**
     * @var Database|null
     */
    private $db;

    /**
     * Application status
     * @var string
     */
    public $status;

    /**
     * @var Settings
     */
    public $config;

    /**
     * @var array|null
     */
    public $settings = array(
        'status'=>'On',
        'siteTitle'=>'DropMVC',
        'siteSubTitle'=>'A simple Php framework',
        'template'=>'default'
    );

    /**
     * @var string
     */
    public static $app_name = 'WebMyResearch';

    /**
     * @var string
     */
    public static $version = "1.0.0";

    /**
     * @var string
     */
    public static $author = 'Florian Perdreau';

    /**
     * @var string
     */
    public static $repository = "github";

    /**
     * @var int
     */
    public static $copyright = 2016;

    /**
     * @var string
     */
    public static $site_url;

    /**
     * @var string
     */
    public static $template = 'default';

    /**
     * @var
     */
    protected $session;

    /**
     * App constructor.
     * @param bool $get
     */
    private function __construct($get=true) {
        $this->db = MySqlDb::getInstance();
        if ($get) {
            $this->loadAppSettings();
        }
    }

    /**
     * Loads application settings
     */
    public function loadAppSettings() {
        $this->config = new Settings($this->db,__CLASS__,$this->settings);
        $this->settings = $this->config->settings;
    }

    /**
     * Get Application instance
     * @param bool $get
     * @return App
     */
    public static function getInstance($get=true) {
        if (is_null(self::$instance)) {
            self::$instance = new self($get);
        }
        return self::$instance;
    }

    /**
     * Factory
     * @param $name
     * @return mixed
     */
    public function getModel($name) {
        $class_name = '\App\Model\\' . ucfirst($name) . 'Model';
        return new $class_name($this->db);
    }

    /**
     * Load Controller settings
     * @param $name
     * @param null $settings
     * @return Settings
     */
    public function loadSettings($name, $settings=null) {
        return new Settings($this->db, $name, $settings);
    }

    /**
     * Application settings getter
     * @param $setting
     * @return mixed
     */
    public function getSettings($setting) {
        return $this->settings[$setting];
    }

    /**
     * Backup application
     */
    public function backup() {
        $backup = new \Core\Backup\Backup($this->db);
        $backup->backupDb(10);
    }

    /**
     * This function gets App's URL to root
     * @param null $lang: language
     * @return string
     */
    public static function getAppUrl($lang=null) {
        if (is_null(self::$site_url) || !is_null($lang)) {
            $root = explode('/',  dirname($_SERVER['PHP_SELF']));
            $root = '/' . $root[1];
            self::$site_url = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' )
                . $_SERVER['HTTP_HOST'] . $root .'/';

            if(substr(self::$site_url, -2) == '//') {
                self::$site_url = substr(self::$site_url, 0, -1);
            }

            if (!(is_null($lang))) {
                self::$site_url .= $lang.'/';
            }
        }
        $_SESSION['BASE_URL'] = self::$site_url;
        return self::$site_url;
    }

    /**
     * This function returns list of templates present in View/templates folder
     * @return array
     */
    public static function getTemplates() {
        $path = PATH_TO_APP . 'app' . DS . 'Views' . DS . 'templates';
        $templates = glob($path . DS . '*' , GLOB_ONLYDIR);
        $result = array();
        foreach ($templates as $template) {
            $split = explode(DS, $template);
            $templateName = end($split);
            $result[ucfirst($templateName)] = $templateName;
        }
        return $result;
    }

    /**
     * This function boots the application
     * @param bool|false $debug
     */
    public static function boot($debug=false) {
        /**
         * Define timezone
         *
         */
        date_default_timezone_set('UTC');

        /**
         * Show PHP errors (debug mode only)
         */
        if ($debug) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', '1');
        } else {
            ini_set('display_errors', '0');
        }


        if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if(!defined('APP_NAME')) define('APP_NAME', basename(dirname(__DIR__)));
        if(!defined('PATH_TO_APP')) define('PATH_TO_APP', dirname(__DIR__) . DS);

        require 'Autoloader.php';
        Autoloader::register();
        require PATH_TO_APP . 'core' . DS . 'Autoloader.php';
        \Core\Autoloader::register();

        if (php_sapi_name() !== "cli") {

            // Start session
            SessionManager::sessionStart(self::$app_name, 3600);

            /**
             * Get App url
             */
            self::getAppUrl();
        }

        /**
         * Define paths
         */
        if(!defined('PATH_TO_INCLUDES')) define('PATH_TO_INCLUDES', PATH_TO_APP. 'inc' . DS);
        if(!defined('PATH_TO_CONTROLLERS')) define('PATH_TO_CONTROLLERS', PATH_TO_APP . 'controllers' . DS);
        if(!defined('PATH_TO_PHP')) define('PATH_TO_PHP', PATH_TO_APP . 'php' . DS);
        if(!defined('PATH_TO_PAGES')) define('PATH_TO_PAGES', PATH_TO_APP . 'app' . DS . 'Views' . DS);
        if(!defined('PATH_TO_CONFIG')) define('PATH_TO_CONFIG', PATH_TO_APP . 'config' . DS);
        if(!defined('PATH_TO_LIBS')) define('PATH_TO_LIBS', PATH_TO_APP . 'vendors' . DS);
        if(!defined('PATH_TO_UPLOADS')) define('PATH_TO_UPLOADS', PATH_TO_APP . 'uploads' . DS);
        if(!defined('PATH_TO_TEMPLATES')) define('PATH_TO_TEMPLATES', PATH_TO_APP . 'templates' . DS);
        if(!defined('URL_TO_APP')) define('URL_TO_APP', self::$site_url);

        //Redefine paths to IMG, CSS, JS
        if(!defined('PATH_TO_IMG')) define('PATH_TO_IMG', self::$site_url.'public/images/');
        if(!defined('PATH_TO_JS')) define('PATH_TO_JS', self::$site_url.'public/js/');
        if(!defined('URL_TO_UPLOADS')) define('URL_TO_UPLOADS', self::$site_url.'uploads/');

    }

    /**
     * Install application
     * @param $op: do we make a new installation (overwriting pre-existent data)
     */
    public function install($op) {
        $modeList = array_diff(scandir(PATH_TO_APP . 'app' . DS . 'Model' . DS), array('..', '.'));
        foreach ($modeList as $modelFile) {
            $modelName = "\\App\\Model\\" . str_replace('.class.php','', $modelFile);
            /**
             * @var BaseModel $model
             */
            $model = new $modelName($this->db);
            $model->setup($op);
        }
    }


}
