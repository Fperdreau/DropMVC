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

namespace App\Views;


use App\Entity\TasksEntity;

/**
 * Class TasksView
 * @package App\Views
 */
class TasksView {

    /**
     * Display jobs list
     * @param TasksEntity $task
     * @return string
     */
    public function show($task) {

        $cronList = "";
        $pluginDescription = (!empty($task->description)) ? $task->description:null;

        $install = ($task->installed) ? URL_TO_APP . "tasks/delete/$task->name":URL_TO_APP . "tasks/install/$task->name";
        $install_op = ($task->installed) ? 'uninstall':'install';
        $install_btn = ($task->installed) ? 'uninstallBtn':'installBtn';

        $on_off = ($task->status === "On") ? "Off":"On";
        $activateBtn = ($task->status === 'On') ? "deactivateBtn" : "activateBtn";

        $datetime = $task->time;
        $date = date('Y-m-d', strtotime($datetime));
        $time = date('H:i', strtotime($datetime));

        $frequency = (!empty($task->frequency)) ? explode(',', $task->frequency): array(0, 0, 0, 0);

        $cronList .= "
        <div class='plugDiv' id='{$task->name}'>
            <div class='plugLeft'>
                <div class='plugName'>{$task->name}</div>
                <div class='plugTime' id='{$task->name}'>{$datetime}</div>
                <div class='optbar'>
                    <div class='optBtn'>
                        <a href='".URL_TO_APP . "tasks/options/$task->name"."' class='optShow' data-op='cron'>
                            <div class='optBtn_icon settingsBtn'></div>
                        </a>
                    </div>
                    <div class='optBtn'>
                        <a href='" . $install . "' class='installDep' data-op='{$install_op}'>
                            <div class='optBtn_icon {$install_btn}'></div>
                        </a>
                    </div>
                    <div class='optBtn'>
                        <a href='" . URL_TO_APP . "tasks/run/$task->name" . "' class='run_cron'>
                            <div class='optBtn_icon runBtn'></div>
                        </a>
                    </div>
                    <div class='optBtn'>
                        <a href='" . URL_TO_APP . "tasks/activate/$task->name/$on_off" . "' class='activateDep' data-op='{$on_off}'>
                            <div class='optBtn_icon $activateBtn'></div>
                        </a>
                    </div>
                </div>
            </div>

            <div class='plugSettings'>
                <div class='description'>
                    {$pluginDescription}
                </div>
                <div>

                    <div class='settings'>
                        <form method='post' action='" . URL_TO_APP . "tasks/modSettings/$task->name" . "'>
                            <div>Date & Time</div>
                            <div class='form-group'>
                                <label>Date</label>
                                <input type='date' name='date' value='{$date}'/>
                            </div>
                            <div class='form-group'>
                                <label>Time</label>
                                <input type='time' name='time' value='{$time}' />
                            </div>
                            
                            <div class='frequency_container'>
    
                                <div>Frequency</div>
                                <div class='form-group'>
                                    <label>Months</label>
                                    <input name='months' type='number' value='{$frequency[0]}'/>
                                </div>
                                <div class='form-group'>
                                    <label>Days</label>
                                    <input name='days' type='number' value='{$frequency[1]}'/>
                                </div>
                                <div class='form-group'>
                                    <label>Hours</label>
                                    <input name='hours' type='number' value='{$frequency[2]}'/>
                                </div>
                                <div class='form-group'>
                                    <label>Minutes</label>
                                    <input name='minutes' type='number' value='{$frequency[3]}'/>
                                </div>
                            </div>
                            <div class='submit_btns'>
                                <input type='hidden' name='modCron' value='{$task->name}'/> 
                                <input type='submit' value='Update' class='modCron'/>
                            </div>
                        </form>

                    </div>

                    <div class='plugOpt' id='$task->name'></div>
                    <div>
                        <a href='" . URL_TO_APP . 'tasks/showlog/' . $task->name . "' class='showLog' id='{$task->name}'><button type='submit'>Show Logs</button></a>
                        <a href='" . URL_TO_APP . 'tasks/deleteLog/' . $task->name . "' class='deleteLog' id='{$task->name}'><button type='submit'>Delete Logs</button></a>
                    </div>

                    <div class='plugLog' id='{$task->name}' style='display: none'></div>

                </div>
                
            </div>
        </div>
        ";
        

        return $cronList;
    }

     /**
     * Display task's settings
     * @param $task
     * @return string
     */
    public function displayOpt($task) {
        $action = URL_TO_APP . 'tasks/updateOptions/'.$task->name;
        $content = "<div class='task_options_header'>Options</div>";
        if (!empty($task->options)) {
            $opt = '';
            foreach ($task->options as $optName=>$settings) {
                if (isset($settings['options']) && !empty($settings['options'])) {
                    $options = "";
                    foreach ($settings['options'] as $prop=>$value) {
                        $options .= "<option value='{$value}'>{$prop}</option>";
                    }
                    $optProp = "<select name='{$optName}'>{$options}</select>";
                } else {
                    $optProp = "<input type='text' name='$optName' value='{$settings['value']}' style='width: auto;'/>";
                }
                $opt .= "
                    <div class='form-group'>
                        <label for='{$optName}'>{$optName}</label>
                        {$optProp}
                    </div>
                ";
            }
            $content .= "
                <form method='post' action='{$action}'>
                {$opt}
                    <input type='submit' class='modOpt' data-op='cron' value='Modify'>
                </form>
                
                ";
        } else {
            $content = "No settings available for this task.";
        }
        return $content;
    }

    /**
     * Display jobs list
     * @param array $jobsList
     * @return string
     */
    public function all(array $jobsList) {
        $cronList = "";
        foreach ($jobsList as $cron) {
            $cronList .= $this->show($cron);
        }
        return $cronList;
    }

    /**
     * Content of notification email sent to admin when a scheduled task has run
     * @param $logs
     * @return string
     */
    public static function notify($logs) {
        return "
            <p>Hello, </p>
            <p>Please find below the logs of the scheduled tasks.</p>
            <div style='display: block; padding: 10px; margin: 0 30px 20px 0; border: 1px solid #ddd; background-color: rgba(255,255,255,1);'>
                <div style='color: #444444; margin-bottom: 10px;  border-bottom:1px solid #DDD; font-weight: 500; font-size: 1.2em;'>
                    Logs
                </div>
                <div style='padding: 5px; background-color: rgba(255,255,255,.5); display: block;'>
                    $logs
                </div>
            </div>";
    }

    /**
     *  Display scheduled task's logs
     * @param string $name: Task's name
     * @return null|string: logs
     */
    public static function showLog($name) {
        $path = PATH_TO_APP . 'app' . DS . 'cronjobs' . DS . 'logs'. DS . $name . '.txt';
        if (is_file($path)) {
            $logs = '';
            $fh = fopen($path,'r');
            while ($line = fgets($fh)) {
                $logs .= "{$line}<br>";
            }
            fclose($fh);
        } else {
            $logs = null;
        }
        return $logs;
    }


}