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

namespace App\Entity;


use Core\Entities\BaseEntity;

/**
 * Class UsersEntity
 * @package App\Entity
 */
class UsersEntity extends BaseEntity {

    public $id;

    // Login
    public $username;
    public $password;

    // Personal information
    public $firstname;
    public $lastname;
    public $fullname;
    public $birthday;
    public $nationality;
    public $title;
    public $position;

    // Contact information
    public $email;
    public $phone;
    public $address;
    public $postcode;
    public $city;
    public $country;
    public $office;
    public $university;
    public $department;
    public $mapurl;
    public $photo;
    public $description;
    
    // Application
    public $status;
    public $hash;
    public $active;
    public $attempt;
    public $last_login;
    public $lang;

}