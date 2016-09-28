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

namespace Core;

use Core\Database\Database;
use Core\Database\MySqlDb;
use Core\Models\BaseModel;

/**
 * Class Settings
 * @package Core
 */
class Settings extends BaseModel {

    protected $tablename = 'settings';
    /**
     * Object name
     * @var null|string
     */
    public $object;

    /**
     * Object settings
     * e.g.: array('setting1'=>value)
     * @var array|null
     */
    public $settings;

    /**
     * Constructor
     * @param Database $db
     * @param string $class_name : object name
     * @param null|array $settings : object's settings
     */
    public function __construct(Database $db, $class_name=null, $settings=null) {
        parent::__construct($db,__CLASS__);
        $split = explode('\\',$class_name);
        $this->object = str_replace('Model','',end($split));
        $this->settings = $settings;
        if ($this->db->tableExists($this->tablename)) {
            $this->load();
            $this->update(array(), array());
        }
    }

    /**
     * Get application settings
     */
    public function load() {
        if (!is_null($this->settings)) {
            foreach ($this->settings as $setting=>$value) {
                $row = $this->db->select($this->tablename,array('variable','value'),array('object'=>$this->object,'variable'=>$setting))->single();
                $this->settings[$setting] = ($row !== false) ? $row['value'] : $value;
            }
        }
    }

    /**
     * Update settings
     * @param array $post : associative array providing new data (varName=>value)
     * @param array $id
     * @return bool
     */
    public function update(array $post, array $id) {
        $result = true;
        if (is_null($this->settings)) {
            var_dump($this->object);
        }
        foreach ($this->settings as $varName=>$value) {
            $value = (!empty($post[$varName])) ? htmlspecialchars($post[$varName]):$value;;
            $exist = $this->db->select($this->tablename,array("variable"),array("variable"=>$varName, "object"=>$this->object))->single();
            if ($exist != false) {
                $result = $this->db->update($this->tablename,array("value"=>$value),array("variable"=>$varName, "object"=>$this->object));
            } else {
                $result =$this->db->insert($this->tablename,array("object"=>$this->object,"variable"=>$varName,"value"=>$value));
            }
        }
        return $result;
    }

}