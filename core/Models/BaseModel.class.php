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

namespace Core\Models;
use Core\Database\Database;
use Core\Settings;

/**
 * Class BaseModel
 * @package Core\Models
 */
abstract class BaseModel {

    /**
     * @var Database
     */
    protected $db;

    /**
     * Table name
     * @var string
     */
    protected $tablename;

    /**
     * Table schema
     * @var array
     */
    protected $table_data;

    /**
     * Model name
     * @var string
     */
    protected $modelName;

    /**
     * Model settings
     * @var array
     */
    protected $settings;

    /**
     * Settings instance
     * @var Settings
     */
    protected $config;

    /**
     * Model entity
     * @var string
     */
    protected $entity;

    /**
     * Constructor
     * @param Database $db
     * @param $class_name
     * @param array $settings
     * @param bool $plugin
     */
    function __construct(Database $db, $class_name, array $settings=array(), $plugin=False) {
        $this->db = $db;
        $table_name = $this->getTableName();
        $this->tablename = $plugin !== False ? $this->db->getConfig('dbprefix') . '_' . $plugin : $this->db->getConfig('dbprefix').'_'.$table_name;
        $this->table_data = self::get_table_data($table_name);
    }

    /**
     * Retrieve all elements from the selected table
     * @param array $id
     * @param array $filter
     * @return array|mixed
     */
    public function all(array $id=array(), array $filter=null) {
        $dir = (!is_null($filter) && isset($filter['dir'])) ? strtoupper($filter['dir']):'DESC';
        $param = (!is_null($filter) && isset($filter['order'])) ? "ORDER BY `{$filter['order']}` ".$dir:null;
        return $this->db->select($this->tablename, array('*'), $id, null, $param)->resultset($this->entity);
    }

    /**
     * Get information from db
     * @param array $id
     * @return bool|mixed
     */
    public function get(array $id) {
        return $this->db->select($this->tablename,array('*'), $id)->single($this->entity);
    }

    /**
     * Get item inform by its translated name
     * @param array $id : identifier (e.g.: array('name'=>'name_of_item'))
     * @param  null|string $column
     * @return mixed
     */
    public function getByName($id, $column=null) {
        $key = array_keys($id);
        $key = $key[0];
        $sql = "SELECT *
                FROM {$this->tablename}
                WHERE {$key} LIKE '%{$id[$key]}%'
        ";
        return $this->db->query($sql)->single($this->entity);
    }

    /**
     * Add to the db
     * @param $post
     * @return mixed
     */
    public function add($post) {
        return $this->db->insert($this->tablename, $this->parsenewdata($post));
    }

    /**
     * Update information
     * @param array $post
     * @param array $id
     * @return bool|string
     */
    public function update(array $post, array $id) {
        return $this->db->update($this->tablename, $this->parsenewdata($post), $id);
    }

    /**
     * Delete element
     * @param array $id
     * @return mixed
     */
    public function delete(array $id=array()) {
        return $this->db->delete($this->tablename, $id);
    }

    /**
     * Checks if id exists in a column
     * @param array $id: array('column_name'=>'id')
     * @param null $tablename
     * @return bool
     */
    public function is_exist(array $id, $tablename=null) {
        $table_name = (is_null($tablename)) ? $this->tablename:$tablename;
        $data = $this->db->select($table_name, array('*'), $id)->single();
        return $data !== false;
    }

    /**
     * Get new instance of Settings
     * @return Settings
     */
    protected function getConfig() {
        $this->config = new Settings($this->db, get_class($this), $this->settings);
    }

    /**
     * Load entity
     */
    protected function loadEntity() {
        $entity = str_replace('Model','Entity',get_class($this));
        $this->entity = $entity;
    }

    /**
     * This function returns database table name from class name
     * @return string
     */
    protected function getTableName() {

        $parts = explode('\\', get_class($this));
        $class_name = end($parts);
        return strtolower(str_replace('Model', '', $class_name));
    }

    /**
     * This function returns table information from schema.php file
     * @param string $tableName
     * @return array
     */
    protected static function get_table_data($tableName) {
        $tables_data = require(PATH_TO_APP . 'config' . DS . 'schema.php');
        return $tables_data[$tableName];
    }

    /**
     * Parse new date
     * @param array $post
     * @return array
     */
    protected function parsenewdata($post=array()) {
        $this->loadEntity();
        $content = array();
        foreach ($post as $name=>$value) {
            if (property_exists($this->entity, $name)) {
                $value = (is_array($value)) ? json_encode($value):htmlspecialchars($value);
                $content[$name] = $value;
            }
        }
        return $content;
    }

    /**
     * Create specific ID for new item
     * @param $refId
     * @return string
     */
    public function generateID($refId) {
        $id = date('Ymd').rand(1,10000);

        // Check if random ID does not already exist in our database
        $prev_id = $this->db->select($this->tablename,array($refId))->column();
        while (in_array($id,$prev_id)) {
            $id = date('Ymd').rand(1,10000);
        }
        return $id;
    }

    /**
     * Create or update table
     * @param bool $op
     * @param null $table_name
     * @param null $table_id
     * @return mixed
     */
    public function setup($op=False, $table_name=null, $table_id=null) {
        $table_name = (is_null($table_name)) ? $this->tablename:$table_name;
        $table_id = (is_null($table_id)) ? $this->getTableName():$table_id;
        if ($this->makeorupdate($table_name, self::get_table_data($table_id), $op)) {
            $result['status'] = True;
            $result['msg'] = "'$table_name' created";
        } else {
            $result['status'] = False;
            $result['msg'] = "'$table_name' not created";
        }
        return $result;
    }

    /**
     * Create or update a table
     * @param string $tablename: table name
     * @param array $tabledata: table data
     * e.g.  array(
     *           "id"=>array('INT NOT NULL AUTO_INCREMENT',false),
     *           "name"=>array('CHAR(20)',false),
     *           "primary"=>"id"
     *           );
     * @param bool $overwrite: do we overwrite data?
     * @return bool
     */
    protected function makeorupdate($tablename, array $tabledata, $overwrite=false) {
        // Parse table's data
        $columns = array();
        $defcolumns = array();
        foreach ($tabledata as $column=>$data) {
            $defcolumns[] = $column;
            if ($column == "primary") {
                $columns[] = "PRIMARY KEY($data)";
            } else {
                $datatype = $data[0];
                $defaut = (isset($data[1])) ? $data[1]:false;
                $col = "`$column` $datatype";
                if ($defaut != false) {
                    $col .= " DEFAULT '$defaut'";
                }
                $columns[] = $col;
            }
        }
        $columndata = implode(',',$columns);

        // If overwrite, then we simply create a new table and drop the previous one
        if ($overwrite || !$this->db->tableExists($tablename)) {
            $this->db->createtable($tablename,$columndata,$overwrite);
        } else {
            // Get existent columns
            $keys = $this->db->getcolumns($tablename);
            // Add new non existent columns or update previous version
            $prevcolumn = "id";
            foreach ($tabledata as $column=>$data) {
                if ($column !== "primary" && $column !== "id") {
                    $datatype = $data[0];
                    $default = (isset($data[1])) ? $data[1]:false;
                    $oldname = (isset($data[2])) ? $data[2]:false;

                    // Change the column's name if asked and if this column exists
                    if ($oldname !== false && in_array($oldname,$keys)) {
                        $sql = "ALTER TABLE $tablename CHANGE $oldname $column $datatype";
                        if ($default !== false) $sql .= " DEFAULT '$default'";
                        $this->db->run($sql);
                        // If the column does not exist already, then we simply add it to the table
                    } elseif (!in_array($column,$keys)) {
                        $this->db->addcolumn($tablename,$column,$datatype,$prevcolumn);
                        // Check if the column's data type is consistent with the new version
                    } else {
                        $sql = "ALTER TABLE $tablename MODIFY $column $datatype";
                        if ($default !== false) $sql .= " DEFAULT '$default'";
                        $this->db->run($sql);
                    }
                    $prevcolumn = $column;
                }
            }

            // Get updated columns
            $keys = $this->db->getcolumns($tablename);
            // Remove deprecated columns
            foreach ($keys as $key) {
                if (!in_array($key,$defcolumns)) {
                    $sql = "ALTER TABLE $tablename DROP COLUMN $key";
                    $this->db->run($sql);
                }
            }
        }
        return true;
    }


}
