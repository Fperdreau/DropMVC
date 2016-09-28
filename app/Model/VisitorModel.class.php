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
 * Class VisitorModel
 * @package App\Model
 */
class VisitorModel extends BaseModel {

    /**
     * Constructor
     * @param Database $db
     */
    public function __construct(Database $db) {
        parent::__construct($db, __CLASS__);
        $this->loadEntity();
    }

    /**
     * Create Tool
     * @param $post
     * @return bool
     */
    public function add($post) {
        // Parse variables and values to store in the table
        if (!$this->is_exist(array('name'=>$post['name'],'type'=>$post['type'], 'ip'=>$post['ip']))) {
            return $this->db->insert($this->tablename, $this->parsenewdata($post));
        } else {
            return false;
        }
    }
    
}