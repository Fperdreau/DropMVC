<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 14/09/2016
 * Time: 07:19
 */

namespace Core;


use App\Model\TasksModel;

class Task {

    /**
     * @var $default array
     */
    public static $default = array(
        'name'=>null,
        'status'=>'Off',
        'options'=>null,
        'time' => '1970-01-01 00:00:00',
        'frequency' => '0,0,0,0',
        'description' => null
    );

    /**
     * @var TasksModel $Tasks
     */
    protected $Tasks;

    /**
     * Constructor
     * @param TasksModel $Tasks
     */
    public function __construct(TasksModel $Tasks) {
        $this->Tasks = $Tasks;
    }

    /**
     * Run scheduled task: backup the database
     * @return string
     */
    public function start() {
    }

    /**
     * Get info from the tasks table
     * @return bool|mixed
     */
    public function get() {
        $data = $this->Tasks->get(array('name'=>self::$default['name']));
        $data->options = json_decode($data->options, true);
        return $data;
    }


}