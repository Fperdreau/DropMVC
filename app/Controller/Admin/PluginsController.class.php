<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 20/12/2015
 * Time: 08:17
 */

namespace App\Controller\Admin;


class PluginsController extends AppController {

    function __construct()
    {
        parent::__construct();
        $this->loadModel('Plugins');
    }

    function index() {
        
    }

}