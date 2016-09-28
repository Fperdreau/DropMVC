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

namespace Core\Database;
use \PDO;
use PDOException;

/**
 * Class MySqlDb
 * @package Core\Database
 */
class MySqlDb implements Database {

    /**
     * PDO link to DB
     * @var null|PDO
     */
    public $bdd = null;

    /**
     * @var null|MySqlDb
     */
    private static $instance = null;

    /**
     * Default settings
     * @var array
     */
    private static $default = array(
        "version"=>false,
        "dbname"=>"",
        "host"=>"localhost",
        "dbprefix"=>"",
        "username"=>"root",
        "passw"=>""
    );

    /**
     * Settings
     * @var array|bool
     */
    public $config;

    /**
     * DB Charset
     * @var string
     */
    private static $charset = 'utf8';

    /**
     * @var \PDOStatement
     */
    private $stmt; // Query statement

    /**
     * Constructor
     */
    private function __construct() {
        $this->config = self::get_config();
        if ($this->config !== false) {
            $this->connect();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get db config
     * @return bool|array
     */
    public static function get_config() {
        $version_file = PATH_TO_CONFIG."config.php";
        if (is_file($version_file)) {
            require $version_file;
            if (!isset($config)) {
                $config['version'] = isset($version) ? $version : "unknown";
                $config['host'] = $host;
                $config['username'] = $username;
                $config['passw'] = $passw;
                $config['dbname'] = $dbname;
                $config['dbprefix'] = str_replace('_','',$db_prefix);
            }
        } else {
            return false;
            //$config = self::$default;
        }
        return $config;
    }

    /**
     * Get config
     * @param $key
     * @return bool
     */
    public function getConfig($key) {
        if (!isset($this->config[$key])) {
            return false;
        }
        return $this->config[$key];
    }

    /**
     * Connects to DB and throws exceptions
     * @return PDO|null
     */
    public function connect() {
        try {
            $this->bdd = new PDO("mysql:host=".$this->config['host'].";dbname=".$this->config['dbname'].";charset=".self::$charset,$this->config['username'],
                $this->config['passw'], array(
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true));
            $this->bdd->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } catch (PDOException $ex) {
            $result['status'] = false;
            die(json_encode("Failed to connect to the database<br>" . $ex->getMessage()));
        }

        return $this->bdd;
    }

    /**
     * Test credentials and throws exceptions
     * @param array $config
     * @return array
     */
    public static function testdb(array $config) {
        try {
            $bdd = new PDO("mysql:host=".$config['host'].";dbname=".$config['dbname'].";charset=".self::$charset,$config['username'],
                $config['passw'], array(
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true));
            $result['status'] = true;
            $result['msg'] = "Connected";
        } catch (PDOException $ex) {
            $result['status'] = false;
            $result['msg'] = "Failed to connect to the database: ". $ex->getMessage();
        }

        return $result;
    }

    /**
     * Close connection to the db
     * @return null
     */
    public function bdd_close() {
        $this->bdd = null;
    }

    /**
     * Destroy instance
     */
    public static function destroy() {
        self::$instance = null;
    }

    /**
     * This function prepares and execute query to the database
     * @param string $sql : unprepared statement
     * @param null|array $params: array providing query parameters
     * @param bool $silent : verbose
     * @return MySqlDb result
     */
    public function query($sql, array $params=null, $silent=false) {
        if ($params === null) {
            $this->run($sql, $silent);
        } else {
            $this->bind($sql, $params)->execute();
        }
        return $this;
    }

    /**
     * This function executes query
     * @return mixed
     */
    public function execute() {
        return $this->stmt->execute();
    }

    /**
     * This function returns all data in an associative array
     * @param null|string $class_name
     * @return mixed
     */
    public function resultset($class_name=null)
    {
        $this->execute();
        if ($class_name === null) {
            $res = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $res = $this->stmt->fetchAll(PDO::FETCH_CLASS, $class_name);
        }
        $this->stmt->closeCursor();
        return $res;
    }

    /**
     * This function returns row data in an associative array
     * @param null|string $class_name: class instance to update
     * @return mixed
     */
    public function single($class_name=null) {
        $this->execute();
        if ($class_name === null) {
            $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        } else {
            $this->stmt->setFetchMode(PDO::FETCH_CLASS, $class_name);
        }

        $res = $this->stmt->fetch();
        $this->stmt->closeCursor();
        return $res;
    }

    /**
     * This function returns row data in an associative array
     * @return mixed
     */
    public function column() {
        $this->execute();
        $res = $this->stmt->fetchAll(PDO::FETCH_COLUMN);
        $this->stmt->closeCursor();
        return $res;
    }

    /**
     * This function binds parameters to query and test for their type.
     * @param $sql
     * @param array $params
     * @return MySqlDb
     */
    public function bind($sql, array $params) {
        try {
            $this->stmt = $this->bdd->prepare($sql);
        } catch (PDOException $ex) {
            echo json_encode('SQL command: '.$sql.' <br>SQL message: <br>'.$ex->getMessage().'<br>');
        }

        $i = 1;
        foreach ($params as $value) {
            if (is_int($value) ) {
                $this->stmt->bindValue($i, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $this->stmt->bindValue($i, $value, PDO::PARAM_BOOL);
            } elseif (is_null($value)) {
                $this->stmt->bindValue($i, $value, PDO::PARAM_NULL);
            } else {
                $this->stmt->bindValue($i, $value, PDO::PARAM_STR);
            }
            $i++;
        }
        return $this;
    }

    /**
     * This function runs simple query that does not need to be prepared
     * @param $sql
     * @param bool|false $silent
     * @return bool| \PDOStatement
     */
    public function run($sql,$silent=false) {
        try {
            $this->stmt = $this->bdd->query($sql);
            $this->stmt->closeCursor();
            return $this->stmt;
        } catch (PDOException $ex) {
            if ($silent) {
                echo json_encode('SQL command: '.$sql.' <br>SQL message: <br>'.$ex->getMessage().'<br>');
            }
            return false;
        }
    }

    /**
     * insert content (row) to a table
     * @param $table_name
     * @param array $what: e.g.: array('name'=>'John Doe','age'='NA')
     * @return bool
     */
    public function insert($table_name,array $what) {
        // build query
        $placeholders = array();
        foreach ($what as $col=>$value) {
            $cols_name[] = "`$col`";
            $placeholders[] = ":$col";
        }
        $cols_name = implode(',',$cols_name);

        $places = implode(',',$placeholders);
        $sql = "INSERT INTO $table_name ($cols_name) VALUES($places)";
        if ($this->query($sql,$what)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete content from the table
     * @param string $table_name
     * @param array $where (e.g.: array('id'=>1))
     * @return bool
     */
    public function delete($table_name, array $where) {
        $cond = array();
        foreach ($where as $col=>$value) {
            $cond[] = "$col=:$col";
        }

        $cond = count($cond) > 1 ? implode(' AND ', $cond) : implode('', $cond);

        $sql = "DELETE FROM $table_name WHERE $cond"; // PDO STATEMENT
        if ($this->query($sql,$where)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update a row
     * @param string $table_name
     * @param array $what: e.g. array('name'=>'John Doe')
     * @param array $where: e.g. array('id'=>1)
     * @return bool
     */
    public function update($table_name,array $what,array $where=array()) {
        // Merge data
        $params = $what;

        // Parse data to update
        $set = array();
        $values = array();
        foreach ($what as $col=>$value) {
            $set[] = "$col=:$col";
            $values[":$col"] = $value;
        }
        $set = implode(',',$set);

        // Parse conditions
        $nb_ref = count($where);
        $cond = array();
        foreach ($where as $col=>$value) {
            $placeHolder = "where_$col"; // This is to avoid duplicate keys between what and where
            $cond[] = "$col=:$placeHolder";
            $params[$placeHolder] = $value;
        }
        $cond = ($nb_ref > 1) ? implode(' AND ',$cond):$cond[0];

        // Query
        $sql = "UPDATE $table_name SET $set WHERE $cond";
        if ($this->query($sql,$params)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get content from table (given a column and a row(optional))
     * @param string $table_name
     * @param array $what : e.g. array('name','id')
     * @param array $where : e.g. array('city'=>'Paris')
     * @param array|null $op : Array describing logical operators corresponding to the $where array. e.g. array('=','!=')
     * @param null|string $opt: options (e.g. "ORDER BY year")
     * @return $this : associate array
     */
    public function select($table_name,array $what,array $where=array(),array $op=null, $opt="") {
        $cols = implode(',',$what); // format columns name

        // Build query
        $params = null;
        if (!empty($where)) {
            $i = 0;
            $cond = array(); // Condition (e.g.: name=:name)
            $params = array(); // Params (e.g.: 'name'=>'John Doe'
            foreach ($where as $col => $value) {
                $thisOp = ($op == NULL) ? "=" : $op[$i];
                $cond[] = "$col".$thisOp.":$col";
                $params[":$col"] = $value;
                $i++;
            }
            $cond = " WHERE ".implode(' AND ',$cond);
        } else {
            $cond = "";
        }

        $sql = "SELECT $cols FROM $table_name $cond $opt";
        $this->query($sql,$params);
        return $this;
    }

    /**
     * Get list of tables associated to the application
     * @param null $id
     * @return array
     */
    public function getAppTables($id=null) {
        $sql = "SHOW TABLES FROM ".$this->config['dbname']." LIKE '%".$this->config['dbprefix']."%'";
        $appTables = array();
        foreach ($this->query($sql)->column() as $table) {
            $split = explode('_', $table);
            $tableId = end($split);
            $appTables[$tableId] = $table;
        }
        return (is_null($id)) ? $appTables:$appTables[strtolower($id)];
    }

    /**
     * Check if the table exists
     * @param string $table: table name
     * @return bool
     */
    public function tableExists($table) {
        $sql = "SELECT 1
           FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_TYPE='BASE TABLE'
           AND TABLE_NAME='{$table}'";
        $res = $this->query($sql)->column();
        return !empty($res);
    }

    /**
     * Create a table
     * @param $table_name
     * @param $cols_name
     * @param bool $opt
     * @return bool
     */
    public function createtable($table_name, $cols_name, $opt = false) {
        if ($opt) {
            // Drop table if it exists
            $this->deletetable($table_name);
        }

        if (!$this->tableExists($table_name)) {
            // Create table if it does not exist already
            $sql = 'CREATE TABLE '.$table_name.' ('.$cols_name.')';
            $result = ($this->query($sql)) ? true:false;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Drop a table
     * @param $table_name
     * @return bool|mysqli_result
     */
    public function deletetable($table_name) {
        $sql = "DROP TABLE IF EXISTS `{$table_name}`";
        $result = $this->run($sql)->execute();
        return $result;
    }

    /**
     * Empty a table
     * @param $table_name
     * @return bool|mysqli_result
     */
    public function clearTable($table_name) {
        $sql = "TRUNCATE TABLE $table_name";
        $result = ($this->query($sql)) ? true:false;
        return $result;
    }

    /**
     * Get columns names
     * @param $tablename
     * @return array
     */
    public function getcolumns($tablename) {
        $sql = "SHOW COLUMNS FROM $tablename";
        $keys = $this->query($sql)->column();
        return $keys;
    }

    /**
     * Add a column to the table
     * @param string $table_name: table name
     * @param string $col_name: column name
     * @param string $type: data type
     * @param null|string $after: previous column
     * @return bool
     */
    public function addcolumn($table_name,$col_name,$type,$after=null) {
        if (!$this->iscolumn($table_name,$col_name)) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $col_name $type";
            if (null!=$after) {
                $sql .= " AFTER $after";
            }

            $result = ($this->query($sql)) ? true:false;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * This function check if the specified column exists
     * :param string $table: table name
     * :param string $column: column name
     * :return bool
     */
    public function iscolumn($table, $column){
        $cols = $this->getcolumns($table);
        return in_array($column,$cols);
    }

    /**
     * Get primary key of a table
     * @param $tablename
     * @return array
     */
    public function getprimarykeys($tablename) {
        $sql = "SHOW KEYS FROM $tablename WHERE Key_name = 'PRIMARY'";
        $this->query($sql);
        $keys = $this->resultset();
        return $keys;
    }

}