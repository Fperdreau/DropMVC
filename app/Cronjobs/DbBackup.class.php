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

namespace App\Cronjobs;

use App\Controller\TasksController;
use Core\Backup\Backup;

/**
 * Class DbBackup
 *
 * Scheduled task that creates backup of the database and store them in backup/mysql.
 */
class DbBackup extends TasksController {
    
    /**
     * @var array $default: Default settings
     */
    public $default = array(
        'name'=>'DbBackup',
        'status'=>'Off',
        'options'=>array(
            'nb_version'=>array(
                'options'=>array(),
                'value'=>10)
            ),
        'time' => '1970-01-01 00:00:00',
        'frequency' => '0,0,0,0',
        'description' => "Makes backup of the database, saves it into the backup/mysql folder that can be found
    at the root of the JCM application, and sends a copy by email to the admin. It also automatically delete older versions.
    The number of versions to store can be defined in the task's settings"
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Tasks');
    }

    /**
     * Run scheduled task: backup the database
     * @return string
     */
    public function start() {
        $task = $this->get($this->default['name']);
        $fileLink = Backup::backupDb($task->options['nb_version']);
        Backup::mail_backup($fileLink); // Send backup file to admins

        $result['msg'] = "Backup successfully done: $fileLink";
        $result['status'] = true;

        $data = array();
        $data['time'] = self::parseTime($task->time, explode(',', $task->frequency));
        return $result;
    }
}
