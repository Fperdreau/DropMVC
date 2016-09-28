<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 20/12/2015
 * Time: 08:18
 */

namespace App\Model;


use Core\Database\Database;
use Core\Models\BaseModel;

class PluginsModel extends BaseModel {

    /**
     * PluginsModel constructor.
     * @param Database $db
     */
    function __construct(Database $db) {
        parent::__construct($db, __CLASS__);
    }

}