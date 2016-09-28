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


use Core\Database\Database;
use Core\Models\BaseModel;

/**
 * Class SettingsModel
 * @package App\Model
 */
class SettingsModel extends BaseModel {

    /**
     * SettingsModel constructor.
     * @param Database $db
     */
    function __construct(Database $db) {
        parent::__construct($db, __CLASS__);
        $this->loadEntity();
    }

    /**
     * Get settings
     * @param array $id
     * @return array
     */
    public function get(array $id) {
        $result = [];
        if ($this->db->tableExists($this->tablename)) {
            $data = $this->db->select($this->tablename, array('*'), $id)->resultset($this->entity);
            foreach($data as $obj) {
                $result[$obj->variable] = $obj->value;
            }
        }

        return $result;
    }

    /**
     * Update settings
     * @param array $post : associative array providing new data (varName=>value)
     * @param array $id
     * @return bool
     */
    public function update(array $post, array $id) {
        $name = (isset($id['name'])) ?$id['name'] : null;
        $result = true;
        if (!empty($post)) {
            foreach ($post as $varName => $value) {
                $value = (!empty($post[$varName])) ? htmlspecialchars($post[$varName]) : $value;;

                $exist = $this->db->select($this->tablename, array("variable"), array("variable" => $varName, "object" => $name))->single();
                if ($exist !== false) {
                    $result = $this->db->update($this->tablename, array("value" => $value), array("variable" => $varName, "object" => $name));
                } else {
                    $result = $this->db->insert($this->tablename, array("object" => $name, "variable" => $varName, "value" => $value));
                }
            }
        }
        return $result;
    }

}