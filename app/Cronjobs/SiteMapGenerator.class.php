<?php
/**
 * File for class SiteMapGenerator
 *
 * PHP version 5
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of Journal Club Manager.
 *
 * Journal Club Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Journal Club Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Cronjobs;

use App\Controller\SitemapController;
use App\Controller\TasksController;


/**
 * Class SiteMapGenerator
 * @package App\Cronjobs
 */
class SiteMapGenerator extends TasksController {

    /**
     * @var array $default: Default settings
     */
    public $default = array(
        'name'=>'SiteMapGenerator',
        'status'=>'Off',
        'options'=>array(),
        'time' => '1970-01-01 00:00:00',
        'frequency' => '0,0,0,0',
        'description' => "Generates sitemap."
    );

    /**
     * @var SiteMapController
     */
    public $SiteMap;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->SiteMap = new SitemapController();
    }

    /**
     * Sends emails
     * @return mixed
     */
    public function start() {
        $result = $this->SiteMap->generate();
        $result['msg'] = 'Sitemap generated';
        return $result;
    }

}