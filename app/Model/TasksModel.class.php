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

namespace App\Model;

use App\Controller\SettingsController;
use App\Controller\TasksController;
use App\Entity\TasksEntity;
use Core\Database\Database;
use Core\Settings;
use Core\Tasks\Tasks;
use Exception;
use Core\Models\BaseModel;
/**
 * Class TasksModel
 *
 * Handle scheduled tasks and corresponding routines.
 * - installation
 * - update
 * - run
 *
 */
class TasksModel extends BaseModel {

    /**
     * @var Settings
     */
    public $config;

    /**
     * @var array|null
     */
    public $settings = array(
        "notify_admin" => false
    );

    /**
     * Constructor
     * @param Database $db
     */
    public function __construct(Database $db) {
        parent::__construct($db, __CLASS__);
        $this->loadEntity();
        $this->getConfig();
        $this->settings = $this->config->settings;
    }

    /**
     * Delete table associated with a scheduled task/plugin
     * @param $name
     * @return bool
     */
    public function deleteTable($name) {
        $table_name = $this->db->getConfig('dbprefix') . $name;
        return $this->db->deletetable($table_name);
    }

    /**
     * Instantiate a class from class name
     * @param: class name (must be the same as the file name)
     * @return TasksController:
     */
    public static function instantiate($pluginName) {
        $folder = PATH_TO_APP . 'app' . DS . 'Cronjobs' . DS;
        require_once($folder . $pluginName .'.class.php');
        $pluginName = '\\App\Cronjobs\\' . $pluginName;
        return new $pluginName();
    }

    /**
     * Get list of the scheduled tasks to run
     * @return array
     */
    public function getRunningJobs() {
        $now = strtotime(date('Y-m-d H:i:s'));
        $jobs = $this->getJobs();
        $runnningjobs = array();
        foreach ($jobs as $thisJob=>$info) {
            $jobTime = strtotime($info['time']);
            if ($info['installed'] && $info['status'] == 'On' && $now >= $jobTime && $now<=($jobTime+(59*60))) {
                $runnningjobs[] = $thisJob;
            }
        }
        return $runnningjobs;
    }


    /**
     * Get list of scheduled tasks, their settings and status
     * @return array
     */
    public function getJobs() {
        $folder = PATH_TO_APP . 'app' . DS . 'Cronjobs' . DS;
        $cronList = scandir($folder);
        $jobs = array();
        foreach ($cronList as $cronFile) {
            if (!empty($cronFile) && !in_array($cronFile,array('.','..','run.php','logs'))) {
                $name = explode('.',$cronFile);
                $name = $name[0];

                /**
                 * @var TasksController $Task
                 */
                $Task = self::instantiate($name);
                $installed = $this->is_exist(array('name'=>$name));

                if ($installed) {
                    $thisPlugin = $this->get(array('name'=>$name));
                } else {
                    $thisPlugin = new TasksEntity();
                    $thisPlugin->map($Task->default);
                }
                $default = $Task->default;
                $thisPlugin->description = $default['description'];
                $thisPlugin->installed = $installed;
                $jobs[] = $thisPlugin;
            }
        }
        return $jobs;
    }

}