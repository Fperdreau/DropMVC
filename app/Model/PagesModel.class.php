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
use App\App;
use Core\Database\Database;
use Core\Models\TradModel;

/**
 * Class PagesModel
 * @package App\Model
 */
class PagesModel extends TradModel {

    public $settings = array(
        'template'=>"default"
    );

    public $config;
    public $siteTitle;

    /**
     * AppPageModel constructor.
     * @param Database $db
     */
    public function __construct(Database $db) {
        parent::__construct($db, __CLASS__, $this->settings);
        $this->loadEntity();
        $this->getTitle();
    }

    /**
     * Get siteTitle (user's full name) and subtitle (user's academic position)
     */
    private function getTitle() {
        $this->siteTitle = App::getInstance()->getSettings('siteTitle');
    }

    /**
     * Register Page to the table
     * @param array $post
     * @return bool
     */
    public function make($post=array()) {
        return $this->add($post);
    }
    
    /**
     * Gets list of pages registered in the database
     * @return mixed
     */
    public function getInstalledPages() {
        return $this->db->select($this->tablename,array('name'))->column();
    }

    /**
     * Build Menu
     * @param int $status
     * @return array
     */
    public function getSections($status=-1) {
        return $this->db->select($this->tablename,array('*'),array('show_menu'=>1, 'status'=>$status),array('=','<='),"ORDER BY parent, rank ASC")->resultset($this->entity);
    }
}