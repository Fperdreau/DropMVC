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
use App\Entity\UsersEntity;
use Core\Database\Database;
use Core\Entities\BaseEntity;
use Core\Language\Translation;

/**
 * Class TradModel
 * @package Core\Models
 */
class TradModel extends BaseModel {

    protected $tradTable;
    protected $tradTableData;
    protected static $suffix = '_i18n';
    protected static $lang;

    /**
     * Constructor
     * @param Database $db
     * @param $class_name
     * @param array $settings
     * @param bool $plugin
     */
    function __construct(Database $db, $class_name, array $settings=array(), $plugin=False) {
        parent::__construct($db, $class_name, $settings, $plugin);
        $this->tradTable = $this->tablename.self::$suffix;
        $this->tradTableData = self::get_table_data($this->getTableName().self::$suffix);

        self::$lang = (session_status() !== PHP_SESSION_NONE & isset($_SESSION['lang'])) ? $_SESSION['lang'] : Translation::$default_lang;
    }

    /**
     * Insert element in main and translated tables
     * @param $post
     * @return mixed
     */
    public function add($post) {
        $result = $this->db->insert($this->tablename, $this->parsenewdata($post));
        if ($result) {
            $result = $this->db->insert($this->tradTable, $this->parsenewdata($post, $this->tradTableData));
        }
        return $result;
    }

    /**
     * Update information in main and translated tables
     * @param array $post
     * @param array $id
     * @param string $lang
     * @return bool|string
     */
    public function update(array $post, array $id, $lang=null) {
        $lang = (is_null($lang)) ? self::$lang:$lang;

        // Only updates main table's fields that are not present in the corresponding translation table
        $diff = array_diff_key($this->table_data, $this->tradTableData);
        if (!empty($diff)) {
            $result = $this->db->update($this->tablename, $this->parsenewdata($post, $diff), $id);
        } else {
            $result = true;
        }

        if ($result) {
            $id['lang'] = $lang;
            $tradContent = $this->parsenewdata($post, $this->tradTableData);
            if ($this->is_exist($id, $this->tradTable) && !empty($tradContent)) {
                $result = $this->db->update($this->tradTable, $tradContent, $id);
            } else {
                $tradContent = array_merge($tradContent, $id);
                $result = $this->db->insert($this->tradTable, $tradContent);
            }
        }
        return $result;
    }

    /**
     * Delete element from main and translated tables
     * @param array $id
     * @return mixed
     */
    public function delete(array $id=array()) {
        $result = $this->db->delete($this->tablename, $id);
        if ($result) {
            $result = $this->db->delete($this->tradTable, $id);
        }
        return $result;
    }

    /**
     * Get information from db
     * @param array $id
     * @param null|string $lang: language code (e.g. en_EN)
     * @return bool|BaseEntity
     */
    public function get(array $id, $lang=null) {
        $lang = (is_null($lang)) ? self::$lang:$lang;
        $data = $this->db->select($this->tablename, array('*'), $id)->single($this->entity);
        if ($data!==false) {
            if ($this->is_exist(array_merge($id, array('lang'=>$lang)), $this->tradTable)) {
                $id['lang'] = $lang;
                $trad = $this->db->select($this->tradTable, array('*'), $id)->single($this->entity);
                foreach ($trad as $key=>$value) {
                    if (!is_null($value)) {
                        $data->$key = $value;
                    }
                }
            }
        } else {
            $data = new $this->entity();
        }
        $data->lang = (!property_exists($data, 'lang') || is_null($data->lang)) ? $lang:$data->lang;
        return $data;
    }
    
    /**
     * Get all items
     * @param $on
     * @param null $lang
     * @return mixed
     */
    public function getAll($on, $lang=null) {
        $data = array();
        foreach ($this->all() as $key=>$obj) {
            $data[] = $this->get(array($on=>$obj->$on), $lang);
        }
        return $data;
    }

    /**
     * Get item inform by its translated name
     * @param array $id: identifier (e.g.: array('name'=>'name_of_item'))
     * @param $column: relational field name
     * @return mixed
     */
    public function getByName($id, $column=null) {
        $key = array_keys($id);
        $key = $key[0];
        $sql = "SELECT *
                FROM {$this->tablename} t
                    LEFT JOIN {$this->tradTable} p
                    ON t.{$column}=p.{$column}
                WHERE p.{$key} LIKE '%{$id[$key]}%'
        ";
        return $this->db->query($sql)->single($this->entity);
    }


    /**
     * Get information from db
     * @param array $id
     * @return bool|UsersEntity
     */
    public function single(array $id) {
        return $this->db->select($this->tablename, array('*'), $id)->single($this->entity);
    }

    /**
     * Parse new date
     * @param array $post
     * @param null|array $table_data
     * @return array
     */
    protected function parsenewdata($post=array(), $table_data=null) {
        $columns = (!is_null($table_data)) ? array_keys($table_data):array_keys($this->table_data);
        $content = array();
        foreach ($post as $name=>$value) {
            if (in_array($name, $columns)) {
                $value = htmlspecialchars($value);
                $value = (is_array($value)) ? json_encode($value):$value;
                $content[$name] = $value;
            }
        }
        return $content;
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
            if ($this->makeorupdate($this->tradTable, $this->tradTableData, $op)) {
                $result['status'] = True;
                $result['msg'] = "'{$table_name}' created";
            } else {
                $result['status'] = False;
                $result['msg'] = "'{$this->tradTable}' not created";
            }
        } else {
            $result['status'] = False;
        }

        return $result;
    }

}
