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

namespace Core\Entities;

/**
 * Class BaseEntity
 * @package Core\Entities
 */
class BaseEntity {

    /**
     * Magic getter
     * @param $key
     * @return mixed
     */
    public function __get($key){
        $method = 'get' . ucfirst($key);
        $this->$key = $this->$method();
        return $this->$key;
    }

    /**
     * Map array to entity
     * @param array $properties
     */
    public function map(array $properties=array()){
        foreach($properties as $key => $value){
            $this->{$key} = $value;
        }
    }

}