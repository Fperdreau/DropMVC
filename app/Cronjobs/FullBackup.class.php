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
use Core\Tasks\Tasks;


/**
 * Class FullBackup
 *
 * Scheduled task that creates full backups of the web-site (files & database) and store the corresponding archives
 * in backup/complete.
 */
class FullBackup extends TasksController {

    /**
     * @var array $default: Default settings
     */
    public $default = array(
        'name'=>'FullBackup',
        'status'=>'Off',
        'options'=>array(
            'nb_version'=>array(
                'options'=>array(),
                'value'=>10)
        ),
        'time' => '1970-01-01 00:00:00',
        'frequency' => '0,0,0,0',
        'description' => "Makes a backup of the whole application (files and database), saves it into the 
        backup/complete folder and automatically cleans older backups. The number of versions that has to be stored can be 
        defined in the task's settings"
    );
    
    /**
     * FullBackup constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Tasks');
    }

    /**
     * @return string
     */
    public function start() {
        // db backup
        $task = $this->get($this->default['name']);
        $backupFile = Backup::backupDb($task->options['nb_version']);

        Backup::mail_backup($backupFile); // Send backup file to admins

        // file backup
        $zipFile = Backup::backupFiles(); // Backup site files (archive)
        $fileLink = json_encode($zipFile);

        // Write log only if server request
        $result['msg'] = "Full Backup successfully done: $fileLink";
        $result['status'] = true;
        return $result;
    }
}