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

namespace Core\Backup;
use App\Controller\MailController;
use App\Controller\UsersController;
use App\Model\UsersModel;
use Core\Database\Database;
use Core\Database\MySqlDb;
use Core\Mail\Mail;
use ZipArchive;

/**
 * Class Backup
 * @package Core\Backup
 */
class Backup {

    /**
     * Backup database and save it as a *.sql file. Clean backup folder (remove oldest versions) at the end.
     * @param $nbVersion: Number of previous backup versions to keep on the server (the remaining will be removed)
     * @return string : Path to *.sql file
     */
    public static function backupDb($nbVersion){
        $db = MySqlDb::getInstance();

        // Create Backup Folder
        $mysqlrelativedir = 'backup' .DS. 'mysql';
        $mysqlSaveDir = PATH_TO_APP . DS . $mysqlrelativedir;
        $fileNamePrefix = 'fullbackup_'.date('Y-m-d_H-i-s');

        if (!is_dir(PATH_TO_APP . DS . 'backup')) {
            mkdir(PATH_TO_APP . DS . 'backup',0777);
        }

        if (!is_dir($mysqlSaveDir)) {
            mkdir($mysqlSaveDir,0777);
        }

        // Do backup
        /* Store All AppTable name in an Array */
        $allTables = $db->getapptables();
        $return = "";
        //cycle through
        foreach($allTables as $table=>$table_name) {
            $data = $db->select($table_name, array('*'))->resultset();
            $return.= 'DROP TABLE '.$table_name.';';
            $row = $db->query('SHOW CREATE TABLE '.$table_name)->single();
            $return.= "\n\n".$row["Create Table"].";\n\n";
            foreach ($data as $row) {
                $return .= 'INSERT INTO '.$table_name.' VALUES(';

                $j = 0;
                $num_fields = count($row);
                foreach ($row as $column=>$value) {
                    $value = addslashes($value);
                    $value = preg_replace("/\n/","\\n",$value);
                    if (isset($value)) { $return.= '"'.$value.'"' ; } else { $value.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                    $j++;
                }

                $return .= ");\n";
            }
            $return .="\n\n\n";
        }
        $handle = fopen($mysqlSaveDir . DS . $fileNamePrefix.".sql",'w+');
        fwrite($handle,$return);
        fclose($handle);

        self::cleanBackups($mysqlSaveDir,$nbVersion);
        return "$mysqlrelativedir/$fileNamePrefix.sql";
    }


    /**
     * Delete directories
     * @param string $dir
     * @return bool
     */
    public static function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    /**
     * Browse directories
     * @param string $dir
     * @param array $dirsNotToSaveArray
     * @return array
     */
    private static function browse($dir, $dirsNotToSaveArray = array()) {
        $filenames = array();
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                $filename = $dir."/".$file;
                if ($file != "." && $file != ".." && is_file($filename)) {
                    $filenames[] = $filename;
                }

                else if ($file != "." && $file != ".." && is_dir($dir.$file) && !in_array($dir.$file, $dirsNotToSaveArray) ) {
                    $newfiles = self::browse($dir.$file,$dirsNotToSaveArray);
                    $filenames = array_merge($filenames,$newfiles);
                }
            }
            closedir($handle);
        }
        return $filenames;
    }

    /**
     * Check for previous backup and delete the oldest ones
     * @param $mysqlSaveDir: Path to backup folder
     * @param $nbVersion: Number of backups to keep on the server
     */
    private static function cleanBackups($mysqlSaveDir,$nbVersion) {
        $oldBackup = self::browse($mysqlSaveDir);
        if (!empty($oldBackup)) {
            $files = array();
            // First get files date
            foreach ($oldBackup as $file) {
                $fileWoExt = explode('.',$file);
                $fileWoExt = $fileWoExt[0];
                $prop = explode('_',$fileWoExt);
                if (count($prop)>1) {
                    $back_date = $prop[1];
                    $back_time = $prop[2];
                    $formatedTime = str_replace('-',':',$back_time);
                    $date = $back_date." ".$formatedTime;
                    $files[$date] = $file;
                }
            }

            // Sort backup files by date
            krsort($files);

            // Delete oldest files
            $cpt = 0;
            foreach ($files as $date=>$old) {
                // Delete file if too old
                if ($cpt >= $nbVersion) {
                    if (is_file($old)) {
                        unlink($old);
                    }
                }
                $cpt++;
            }
        }
    }

    /**
     * Mail backup file to admins
     * @param $backupFile
     * @return bool
     */
    public static function mail_backup($backupFile) {
        $mail = new MailController();
        $user = new UsersController();
        $user = $user->getAdmin();

        // Send backup via email
        $content = "
            Hello, <br>
            <p>This message has been sent automatically by the server. You may find a backup of your database in attachment.</p>
            ";

        $subject = "Automatic Database backup";
        if ($mail->send(array($user->email), $content, $subject, $backupFile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Full backup routine (files + database)
     * @return string
     */
    public static function backupFiles() {

        $dirToSave = PATH_TO_APP;
        $dirsNotToSaveArray = array(PATH_TO_APP."backup");
        $mysqlSaveDir = PATH_TO_APP.'/backup/mysql';
        $zipSaveDir = PATH_TO_APP.'/backup/complete';
        $fileNamePrefix = 'fullbackup_'.date('Y-m-d_H-i-s');

        if (!is_dir(PATH_TO_APP.'/backup')) {
            mkdir(PATH_TO_APP.'/backup',0777);
        }

        if (!is_dir($zipSaveDir)) {
            mkdir($zipSaveDir,0777);
        }

        system("gzip ".$mysqlSaveDir."/".$fileNamePrefix.".sql");
        system("rm ".$mysqlSaveDir."/".$fileNamePrefix.".sql");

        $zipfile = $zipSaveDir.'/'.$fileNamePrefix.'.zip';

        // Check if backup does not already exist
        $filenames = self::browse($dirToSave,$dirsNotToSaveArray);

        $zip = new ZipArchive();

        if ($zip->open($zipfile, ZIPARCHIVE::CREATE)!==TRUE) {
            return "cannot open <$zipfile>";
        } else {
            foreach ($filenames as $filename) {
                $zip->addFile($filename,$filename);
            }

            $zip->close();
            return "backup/complete/$fileNamePrefix.zip";
        }
    }

}