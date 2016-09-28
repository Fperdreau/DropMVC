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

namespace Core;

/**
 * Class AppConfig
 * This class handles application configuration
 */
class Config {

    private static $instance;
    private $settings = [];
    private static $folders = array('config','uploads');

    public function __construct() {
        $this->id = uniqid();
        $this->settings = require dirname(__DIR__) . '/config/config.php';
    }

    public function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key) {
        if (!isset($this->settings[$key])) {
            return null;
        }
        return $this->settings[$key];
    }

    /**
     * Create configuration files including database credentials and App version
     * @param $post
     * @return string
     */
    public static function createConfig($post) {
        $filename = PATH_TO_CONFIG . "config.php";
        $result = "";
        if (is_file($filename)) {
            unlink($filename);
        }

        // Make folders
        foreach (self::$folders as $folder) {
            $dirname = PATH_TO_APP.'/'.$folder;
            if (is_dir($dirname) === false) {
                if (!mkdir($dirname, 0755)) {
                    $result['status'] = false;
                    $result['msg'] = "Could not create '$folder' directory";
                    return $result;
                }
            }
        }

        // Write configuration information to config/config.php
        $fields_to_write = array("version", "host", "username", "passw", "dbname", "dbprefix");
        $config = array();
        foreach ($post as $name => $value) {
            if (in_array($name, $fields_to_write)) {
                $config[] = '"' . $name . '" => "' . $value . '"';
            }
        }
        $config = implode(',', $config);
        $string = '<?php $config = array(' . $config . '); ?>';

        // Create new config file
        if ($fp = fopen($filename, "w+")) {
            if (fwrite($fp, $string) == true) {
                fclose($fp);
                $result['status'] = true;
                $result['msg'] = "Configuration file created!";
            } else {
                $result['status'] = false;
                $result['msg'] = "Impossible to write";
            }
        } else {
            $result['status'] = false;
            $result['msg'] = "Impossible to open the file";
        }

        return $result;
    }

}