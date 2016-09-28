<?php
/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of DropCMS.
 *
 * DropCMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropCMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DropCMS.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Core\Router;
use App\App;
use \App\Controller;
use Core\Language\Translation;

/**
 * Class Router
 * @package Core\Router
 */
class Router {

    /**
     * @var array $urlData: URI parameters
     */
    private $urlData;

    /**
     * Full path to controller
     * @var string
     */
    private $pathToController;

    /**
     * @var string: controller to call
     */
    private $controller = 'Home';

    /**
     * @var string: action to call
     */
    private $action = 'index';

    /**
     * Router constructor.
     * @param string $urlData
     */
    function __construct($urlData) {
        $this->urlData = self::parseUri($urlData);
        $this->pathToController = PATH_TO_APP . 'app' . DS . 'Controllers' . DS;
        $this->translations = new Translation();
    }

    /**
     * This function parses and cleans URI
     * @param $urlData
     * @return array
     */
    private static function parseUri($urlData) {
        $params = array_filter(explode('/', $urlData));
        return self::clean($params);
    }

    /**
     * This function cleans URI
     * @param array $urlData
     * @return array
     */
    private static function clean(array $urlData) {
        foreach ($urlData as $key=>$value) {
            $urlData[$key] = htmlspecialchars($value);
        }
        return $urlData;
    }

    /**
     * This function loads controller and action
     */
    public function loadController() {
        $id = [];
        $params = [];

        // Get languages
        if (!empty($this->urlData[0]) && in_array($this->urlData[0], array_keys(Translation::getLanguages()))) {
            $lang = $this->urlData[0];
            $this->translations->set($this->urlData[0]);
            $this->urlData = array_slice($this->urlData, 1);

            // Redefine base route to app
            $_SESSION['BASE_URL'] = App::$site_url.$lang.'/';
        }

        // Get controller and action
        if (!empty($this->urlData[0])) {
            $start = (strtolower($this->urlData[0]) == "admin") ? 1:0;
            if ($start == 1) {
                $this->controller = 'Home';
            }
            for ($i=$start; $i<count($this->urlData); $i++) {
                $key = $this->urlData[$i];
                if ($i == $start) {
                    $this->controller = $key;
                } elseif ($i == $start + 1) {
                    $this->action = $key;
                } elseif ($i == $start + 2) {
                    $id[] = $key;
                } else {
                    $id[] = $key;
                }
            }
        }

        // Call controller or throw error 404 if not found
        $admin = (!empty($this->urlData[0]) && strtolower($this->urlData[0]) === "admin") ? 'Admin\\' : null;
        $controllerName = "\\App\\Controller\\" . $admin . ucfirst($this->controller) . 'Controller';
        if (class_exists($controllerName, true)) {
            $Controller = new $controllerName();
            if (method_exists($controllerName, $this->action)) {
                call_user_func_array(array($Controller,$this->action),$id);
            } else {
                try {
                    call_user_func_array(array($Controller,'index'),array_merge(array($this->action), $id));
                } catch (\Exception $e) {
                    $error = new Controller\ErrorController('404');
                }
            }
        } else {
            $error = new Controller\ErrorController('404');
        }

    }

}