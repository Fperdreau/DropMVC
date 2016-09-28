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

/**
 * Interface Database
 * @package Core\Database
 */
interface Database {

    static function getInstance();

    static function get_config();

    /**
     * Get config
     * @param $key
     * @return bool
     */
    function getConfig($key);

    /**
     * Connects to DB and throws exceptions
     * @return mixed|null
     */
    function connect();

    /**
     * Test credentials and throws exceptions
     * @param array $config
     * @return array
     */
    static function testdb(array $config);

    /**
     * Close connection to the db
     * @return null
     */
    function bdd_close();

    /**
     * Destroy instance
     */
    static function destroy();

    /**
     * This function prepares and execute query to the database
     * @param string $sql : unprepared statement
     * @param null|array $params: array providing query parameters
     * @param bool $silent : verbose
     * @return MySqlDb result
     */
    function query($sql, array $params=null, $silent=false);

    /**
     * This function executes query
     * @return mixed
     */
    function execute();

    /**
     * This function returns all data in an associative array
     * @param null|string $class_name
     * @return mixed
     */
    function resultset($class_name=null);

    /**
     * This function returns row data in an associative array
     * @param null|string $class_name: class instance to update
     * @return mixed
     */
    function single($class_name=null);

    /**
     * This function returns row data in an associative array
     * @return mixed
     */
    function column();

    /**
     * This function binds parameters to query and test for their type.
     * @param $sql
     * @param array $params
     * @return MySqlDb
     */
    function bind($sql, array $params);

    /**
     * This function runs simple query that does not need to be prepared
     * @param $sql
     * @param bool|false $silent
     * @return bool| \PDOStatement
     */
    function run($sql,$silent=false);

    /**
     * insert content (row) to a table
     * @param $table_name
     * @param array $what: e.g.: array('name'=>'John Doe','age'='NA')
     * @return bool
     */
    function insert($table_name,array $what);

    /**
     * Delete content from the table
     * @param string $table_name
     * @param array $where (e.g.: array('id'=>1))
     * @return bool
     */
    function delete($table_name, array $where);

    /**
     * Update a row
     * @param string $table_name
     * @param array $what: e.g. array('name'=>'John Doe')
     * @param array $where: e.g. array('id'=>1)
     * @return bool
     */
    function update($table_name,array $what,array $where=array());

    /**
     * Get content from table (given a column and a row(optional))
     * @param string $table_name
     * @param array $what : e.g. array('name','id')
     * @param array $where : e.g. array('city'=>'Paris')
     * @param array|null $op : Array describing logical operators corresponding to the $where array. e.g. array('=','!=')
     * @param null|string $opt: options (e.g. "ORDER BY year")
     * @return $this : associate array
     */
    function select($table_name,array $what,array $where=array(), array $op=null, $opt="");

    /**
     * Get list of tables associated to the application
     * @param null $id
     * @return array
     */
    function getAppTables($id=null);

    /**
     * Check if the table exists
     * @param string $table: table name
     * @return bool
     */
    function tableExists($table);

    /**
     * Create a table
     * @param $table_name
     * @param $cols_name
     * @param bool $opt
     * @return bool
     */
    function createtable($table_name, $cols_name, $opt = false);

    /**
     * Drop a table
     * @param $table_name
     * @return bool
     */
    function deletetable($table_name);

    /**
     * Empty a table
     * @param $table_name
     * @return bool
     */
    function clearTable($table_name);

    /**
     * Get columns names
     * @param $tablename
     * @return array
     */
    function getcolumns($tablename);

    /**
     * Add a column to the table
     * @param string $table_name: table name
     * @param string $col_name: column name
     * @param string $type: data type
     * @param null|string $after: previous column
     * @return bool
     */
    function addcolumn($table_name,$col_name,$type,$after=null);

    /**
     * This function check if the specified column exists
     * :param string $table: table name
     * :param string $column: column name
     * :return bool
     */
    function iscolumn($table, $column);

    /**
     * Get primary key of a table
     * @param $tablename
     * @return array
     */
    function getprimarykeys($tablename);

}