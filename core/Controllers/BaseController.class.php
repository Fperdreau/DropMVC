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

namespace Core\Controllers;
use App\App;
use Core\Views\BaseView;

/**
 * Class BaseController
 * @package Core\Controllers
 */
class BaseController {

    /**
     * @var string $action
     */
    protected $action;

    /**
     * @var BaseView $view
     */
    protected $view;

    /**
     * @var string $viewPath
     */
    protected $viewPath;

    /**
     * @var null|string $template
     */
    protected $template;

    /**
     * BaseController constructor.
     * @param null $template
     */
    protected function __construct($template=null) {
        if (!is_null($template)) {
            $this->template = $template;
        }
        $this->viewPath = PATH_TO_APP . 'app' . DS . 'Views' . DS;
        if(!defined('PATH_TO_CSS')) define('PATH_TO_CSS', URL_TO_APP . 'app/Views/templates/' . $this->template . '/css/');
    }

    /**
     * Loads controller's associated model
     * 
     * @param string $model: model base name
     */
    protected function loadModel($model) {
        $this->$model = App::getInstance()->getModel($model);
    }

    /**
     * Loads controller's associated view
     * 
     * @param string $view: view's name
     * @return BaseView
     */
    protected function loadView($view) {
        $view = "\\App\\Views\\" . $view . 'View';
        $this->view = new $view();
        return $this->view;
    }

    /**
     * Gets controller full name (including namespace)
     * 
     * @param string $class_name
     * @return string: controller name
     */
    protected static function getControllerName($class_name) {
        $class_name = end(explode('\\', $class_name));
        $class_name = str_replace('Controller', '', $class_name);
        return "\\App\\Controllers\\" . $class_name . 'View';
    }

    /**
     * Renders view
     * 
     * @param string $view: view name
     * @param array $variables
     * @param bool $allowed
     */
    public function render($view, $variables=[], $allowed=true) {
        ob_start();

        if (!empty($variables)) {
            extract($variables);
        }

        if ($allowed) {
            //echo $View->{$view}($variables);
            $view = str_replace('.', DS, $view);
            require($this->viewPath . $view . '.php');
        } else {
            $this->forbidden();
        }

        $pagecontent = ob_get_clean();
        require($this->viewPath . 'templates' . DS . $this->template . DS . 'template.php');

        if (!empty($_POST['page'])) {
            echo json_encode($pagecontent);
            exit;
        }
    }

    /**
     * Renders ERROR 403
     */
    protected function forbidden(){
        header('HTTP/1.0 403 Forbidden');
        $this->render('error.403');
    }

    /**
     * Renders ERROR 404
     */
    protected function notFound(){
        header('HTTP/1.0 404 Not Found');
        $this->render('error.404');

    }

    /**
     * Sanitize array
     * @param array $array
     * @param bool $decode
     * @return array
     */
    protected static function sanitize(array $array, $decode=true) {
        foreach ($array as $key=>$value) {
            $array[$key] = ($decode) ? htmlspecialchars_decode($value):htmlspecialchars($value);
        }
        return $array;
    }

    /**
     * Get's model's settings
     * 
     * @param string $model
     * @param string $setting_name
     * @return mixed
     */
    public function getSettings($model, $setting_name) {
        return $this->$model->settings[$setting_name];
    } 


}