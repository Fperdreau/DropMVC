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

namespace App\Entity;


use Core\Entities\BaseEntity;

/**
 * Class TasksEntity
 * @package App\Entity
 */
class TasksEntity extends BaseEntity {

    /**
     * @var string $name: Task's name
     */
    public $name;

    /**
     * @var string $time: running time
     */
    public $time = '1970-01-01 00:00:00';

    /**
     * @var string $frequency: running frequency (format: 'month,days,hours,minutes')
     */
    public $frequency = '0,0,0,0';

    /**
     * @var string $path: path to script
     */
    public $path;

    /**
     * @var int $status: Task's status (0=>Off, 1=>On)
     */
    public $status = 0;

    /**
     * @var int $installed: is the task registered into the DB (1: yes, 0: no)
     */
    public $installed = 0;

    /**
     * Task's settings
     * Must be formatted as follows:
     *     $options = array(
     *                       'setting_name'=>array(
     *                     'options'=>array(),
     *                     'value'=>0)
     *                );
     *     'options': if not an empty array, then the settings will be displayed as select input. In this case, options
     * must be an associative array: e.g. array('Yes'=>1, 'No'=>0). If it is empty, then it will be displayed as a text
     * input.
     * @var array $options
     */
    public $options=array();

    /**
     * Task's description
     * @var string $description
     */
    public $description;

}