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

namespace App\Model;
use App\Controller\MediaController;
use App\Entity\UsersEntity;
use Core\Database\Database;
use Core\Models\BaseModel;
use Core\Models\TradModel;
use DateTime;
require_once PATH_TO_APP.'core/Auth/PasswordHash.php';

class UsersModel extends TradModel {

    /**
     * Constructor
     * @param Database $db
     */
    function __construct(Database $db) {
        parent::__construct($db, __CLASS__);
        $this->loadEntity();
    }

    /**
     * Create user
     * @param $post
     * @return bool
     */
    public function make($post) {
        $username = htmlspecialchars($post['username']);
        $email = htmlspecialchars($post['email']);
        $post['password'] = self::crypt_pwd($post['password']);
        $post['hash'] = self::create_hash();
        $post['last_login'] = date("Y-m-d H:i:s");
        // Automatically activate account for admin
        if ($post['status'] == 'admin') {
            $post['active'] = 1;
        }
        // Parse variables and values to store in the table
        $content = $this->parsenewdata($post);
        if (!$this->is_exist(array('username'=>$username)) && !$this->is_exist(array('email'=>$email))) {
            // Add to user table
            $this->db->insert($this->tablename, $content);
            $result['status'] = true;
            $result['msg'] = "Your account has been created.";
        } else {
            $result['status'] = false;
            $result['msg'] = "This username/email address already exist in our database";
        }
        return $result;
    }


    /**
     * Find admin user and retrieve its information
     * @return mixed
     */
    public function getAdmin() {
        $sql = "SELECT *
                FROM {$this->tablename} p
                WHERE p.status='admin'";
        return $this->db->query($sql)->single($this->entity);
    }

    /**
     * Encrypt password
     * @param $password
     * @return string
     */
    private function crypt_pwd($password) {
        $hash = create_hash($password);
        return $hash;
    }

    /**
     * Generate random 32 character hash and assign it to a local variable.
     * @return string
     */
    private static function create_hash() {
        $hash = md5( rand(0,1000) );
        return $hash;
    }

    /**
     * Activate or deactivate the user's account
     *
     * @param string $hash
     * @param string $email
     * @param $option
     * @return string
     */
    public function activation($hash, $email, $option) {
        if ($this->db->update($this->tablename,array('active'=>$option),array("hash"=>$hash, "email"=>$email))) {
            $status = ($option == 1) ? 'activated':'deactivated';
            $result['status'] = true;
            $result['msg'] = "Account successfully $status";
        } else {
            $result['status'] = false;
        }
        return $result;
    }

}