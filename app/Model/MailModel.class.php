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
 * Class MailModel
 * @package App\Model
 */
class MailModel extends BaseModel {

    /**
     * MailModel constructor.
     * @param Database $db
     */
    function __construct(Database $db){
        parent::__construct($db, __CLASS__);
        $this->loadEntity();
    }

    /**
     * Delete oldest entries
     * @param $day
     * @return mixed
     */
    public function deleteOldest($day) {
        $date_limit = date('Y-m-d H:i:s',strtotime("now - $day day"));
        $sql = "DELETE FROM {$this->tablename} WHERE date < '{$date_limit}'";
        return $this->db->query($sql)->resultset($this->entity);
    }

}