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

use App\Model\TasksModel;
use App\Views\TasksView;
use DateInterval;
use DateTime;
use Exception;

/**
 * Class TasksController
 *
 * Handle scheduled tasks and corresponding routines.
 * - installation
 * - update
 * - run
 *
 */
class TasksController extends AppController {

    /**
     * @var TasksModel
     */
    protected $Tasks;

    /**
     * @var TasksView
     */
    protected $view;

    /**
     * @var $default array
     */
    public $default = array(
        'name'=>null,
        'status'=>'Off',
        'options'=>null,
        'time' => '1970-01-01 00:00:00',
        'frequency' => '0,0,0,0',
        'description' => null
    );

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Tasks');
        $this->loadView('Tasks');
    }

    /**
     * Run scheduled task
     * @param string $name: task name
     */
    public function run($name) {
        $task = TasksModel::instantiate($name);
        $result = $task->start();
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }


    /**
     * Run scheduled tasks and send a notification to the admins
     * @return bool
     * @throws Exception
     */
    public function execute() {
        $runningCron = $this->getRunningJobs();
        $nbJobs = count($runningCron);
        echo "There are $nbJobs task(s) to run.\n";

        if ($nbJobs > 0) {
            $logs = "There are $nbJobs task(s) to run.\n";
            foreach ($runningCron as $key=>$task) {
                echo "<p>Running '{$task->name}'...</p>";
                $result = null;

                // Instantiate job object
                $thisJob = TasksModel::instantiate($task->name);
                $task_data = $thisJob->get($task->name);

                // Run job
                try {
                    $result = $this->run($task->name);
                    echo $result['msg'];
                    $logs .= "<p>".date('[Y-m-d H:i:s]') . " {$task->name}: {$result['msg']}</p>";
                } catch (Exception $e) {
                    $logs .= "<p>Job '{$task->name}' encountered an error: ".$e->getMessage()."</p>";
                }

                // Update new running time
                $newTime = $thisJob->updateTime($task->name, array('time'=>$task_data->time,
                    'frequency'=>$task_data->frequency));
                if ($newTime['status']) {
                    $logs .= "<p>".date('[Y-m-d H:i:s]') . " $task->name: Next running time: ".$newTime['msg']."</p>";
                } else {
                    $logs .= "<p>".date('[Y-m-d H:i:s]') . " $task->name: Could not update the next running time</p>";
                }

                // Write log
                $this->logger("$task_data->name.txt", $result['msg']);
                echo $logs;
                echo "<p>...Done</p>";
            }
            return $logs;
        } else {
            return false;
        }
    }

    /**
     * Gets task's settings form if the requested task is installed
     * @param string $name: task name
     * @return string
     */
    public function options($name) {
        if ($this->Tasks->is_exist(array('name'=>$name))) {
            $cron = $this->get($name);
            $opt = $this->view->displayOpt($cron);
        } else {
            $opt = '<div class="msg_warning">You must install this task first!</div>';
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($opt);
        }
        return $opt;
    }

    /**
     * Get info from the tasks table
     * @param string $name
     * @return bool|mixed
     */
    public function get($name) {
        $data = $this->Tasks->get(array('name'=>$name));
        $data->options = json_decode($data->options, true);
        return $data;
    }

    /**
     * Update task's information
     * @param $name
     * @return bool|string
     */
    public function update($name) {
        return $this->Tasks->update($_POST, array('name'=>$name));
    }

    /**
     * Update task
     * @param string $name
     * @return bool
     */
    public function updateOptions($name) {
        if ($this->Tasks->is_exist(array('name'=>$name))) {
            $task = $this->get($name);
            $options = $task->options;
            foreach ($_POST['data'] as $key=>$settings) {
                $options[$settings['name']]['value'] = $settings['value'];
            }
            if ($this->Tasks->update(array('options'=>$options), array('name'=>$name))) {
                $result['status'] = true;
                $result['msg'] = "$name's settings successfully updated!";
            } else {
                $result['status'] = true;
            }

        } else {
            $result['status'] = false;
            $result['msg'] = 'You must install this task first!';
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Modify task's settings
     * @param $name
     * @return bool
     */
    public function modSettings($name) {
        if ($this->Tasks->is_exist(array('name'=>$name))) {
            $_POST['time'] = date('Y-m-d H:i:s', strtotime($_POST['date'] . ' ' . $_POST['time']));
            $frequency = array($_POST['months'], $_POST['days'], $_POST['hours'], $_POST['minutes']);
            $_POST['frequency'] = implode(',', $frequency);
            $result['status'] = $this->Tasks->update($_POST, array('name'=>$name));
            $result['msg'] = $_POST['time'];
        } else {
            $result['status'] = False;
            $result['msg'] = 'You must install this task first!';
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Activate/Deactivate task
     * @param string $name: task name
     * @param string $op: new status ('On','Off')
     * @return mixed
     */
    public function activate($name, $op) {
        $result['status'] = $this->Tasks->update(array('status'=>$op), array('name'=>$name));
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Install tasks
     * @param string $name: task name
     * @return mixed
     */
    public function install($name=null) {
        $cron = TasksModel::instantiate($name);
        $result['status'] = $cron->register();
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Install Task
     * @return bool
     */
    public function register() {
        $data = $this->default;
        $data['time'] = self::parseTime($data['time'], explode(',', $data['frequency']));
        return $this->Tasks->add($data);
    }

    /**
     * Delete tasks from DB
     * @param string $name: task name
     * @return bool
     */
    public function delete($name) {
        $result['status'] = $this->Tasks->delete(array('name'=>$name));
        if ($result['status']) {
            $result['status'] = $this->Tasks->deleteTable($name);
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Get next running time from day number, day name and hour
     * @param string $time
     * @param array $frequency
     * @return string: updated running time
     */
    static function parseTime($time, array $frequency) {
        $strtime = date('Y-m-d H:i:s', strtotime($time));
        $date = new DateTime($strtime);
        $date->add(new DateInterval("P{$frequency[0]}M{$frequency[1]}DT{$frequency[2]}H{$frequency[3]}M0S"));
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Update scheduled time
     * @param string $name: Task's name
     * @param null|array $data
     * @return bool
     */
    function updateTime($name, $data=null) {
        $data = (is_null($data)) ? $_POST : $data;
        $data['time'] = self::parseTime($data['time'], explode(',', $data['frequency']));
        if ($this->Tasks->update($data, array('name'=>$name))) {
            $result['status'] = true;
            $result['msg'] = $data['time'];
        } else {
            $result['status'] = false;
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
    }

    /**
     * Write logs into file
     * @param $file
     * @param $string
     */
    static function logger($file, $string) {
        $cronlog = PATH_TO_APP."app/Cronjobs/logs/$file";
        var_dump($cronlog);
        if (!is_dir(PATH_TO_APP.'app/Cronjobs/logs')) {
            mkdir(PATH_TO_APP.'app/Cronjobs/logs',0777);
        }
        if (!is_file($cronlog)) {
            $fp = fopen($cronlog,"w+");
        } else {
            $fp = fopen($cronlog,"a+");
        }
        $string = "[" . date('Y-m-d H:i:s') . "]: $string.\r\n";

        try {
            fwrite($fp,$string);
            fclose($fp);
        } catch (Exception $e) {
            echo "<p>Could not write file '$cronlog':<br>".$e->getMessage()."</p>";
        }
    }

    /**
     * Sends an email to admins with the scheduled tasks logs
     * @param $result
     * @return mixed
     */
    public function notify_admin($result) {
        $admin = new UsersController();
        $admin = $admin->getAdmin();

        $Mail = new MailController();

        $content = $this->view->notify($result);
        return $Mail->send(array($admin->email), $Mail->format($content), 'Scheduled tasks logs');
    }
    
    /**
     * Get list of the scheduled tasks to run
     * @return array
     */
    public function getRunningJobs() {
        $now = new DateTime();
        $jobs = $this->Tasks->getJobs();
        $running_jobs = array();
        foreach ($jobs as $key=>$task) {
            $jobTime = new DateTime($task->time);
            $maxJobTime = new DateTime($task->time);
            $maxJobTime->add(new DateInterval("PT59M"));
            if ($task->installed && $task->status == 'On' && $now >= $jobTime && $now<=$maxJobTime) {
                $running_jobs[] = $task;
            }
        }
        return $running_jobs;
    }

    /**
     * Display task's logs
     * @param $name
     */
    public function showLog($name) {
        $result = $this->view->showLog(htmlspecialchars($name));
        if (is_null($result)) $result = 'Nothing to display';
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
    }

    /**
     *  Delete scheduled task's logs
     * @param string $name: Task's name
     * @return null|string: logs
     */
    public static function deleteLog($name) {
        $path = PATH_TO_APP . '/cronjobs/logs/'. $name . '.txt';
        if (is_file($path)) {
            $result['status'] = unlink($path);
        } else {
            $result['status'] = false;
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
        }
        return $result;
        
    }

}