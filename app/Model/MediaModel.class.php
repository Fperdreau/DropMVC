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

use App\Entity\MediaEntity;
use Core\Database\Database;
use Core\Models\BaseModel;

/**
 * Class MediaModel
 * @package App\Model
 */
class MediaModel extends BaseModel{

    /**
     * File types
     * @var array
     */
    public static $types = array(
        'images'=>array('png','jpg','jpeg','gif','bmp'),
        'docs'=>array('pdf','doc','docx','ppt','pptx','opt','odp')
    );

    protected $directory;
    protected $maxsize;
    protected $allowed_types;

    public $settings = array(
        "upl_types" => "pdf,doc,docx,ppt,pptx,opt,odp",
        "upl_maxsize" => 10000000
    );
    public $config;

    /**
     * Constructor
     * @param Database $db
     */
    function __construct(Database $db) {
        parent::__construct($db, __CLASS__);
        $this->loadEntity();

        $this->getConfig();
        $this->settings = $this->config->settings;
        $this->directory = PATH_TO_UPLOADS;
        $this->maxsize = $this->settings['upl_maxsize'];
        $this->allowed_types = explode(',',$this->settings['upl_types']);
        // Create uploads folder if it does not exist yet
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }
    }

    /**
     * Delete all files associated to an unique id
     * @param array $id
     * @return bool
     */
    public function delete_all(array $id) {
        $data = $this->all($id);
        foreach ($data as $file) {
            if ($this->delete(array('fileid'=>$file->fileid))) {
                return $this->delete_file($file);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Delete file from server
     * @param MediaEntity $file
     * @return bool|string
     */
    private function delete_file(MediaEntity $file) {
        // First delete file from the server
        if (is_file($this->directory.$file->filename)) {
            if (unlink($this->directory . $file->filename)) {
                $result['status'] = true;
                $result['msg'] = "File Deleted";
            } else {
                $result['status'] = false;
                $result['msg'] = "Could not delete the file!";
            }
            return $result;
        }
        return false;
    }

    /**
     * Get uploads associated to an unique id
     * @param array $id
     * @return array
     */
    function get_uploads(array $id) {
        $data = $this->all($id);
        $uploads = array();
        foreach ($data as $file) {
            $uploads[$file->id] = array('date'=>$file->date,'filename'=>$file->filename,'type'=>$file->type);
        }
        return $uploads;
    }

    /**
     * Deletes Db media entry if the corresponding file does not exit on the server
     * 
     * @return bool
     */
    public function clean() {
        // First pass: check files present on server and remove them if not registered
        $dir = PATH_TO_UPLOADS;
        $list = scandir($dir);

        foreach ($list as $file) {
            $data = $this->get(array('filename'=>$file));
            if (!in_array($file, array('.', '..')) && $data === false) {
                if (!unlink($dir . $file)) {
                    echo "Could not delete file {$file} from server";
                }
            }
        }

        // Second pass: check files present in database and remove then if not present on the server
        foreach ($this->all() as $key=>$file) {
            if (!is_file(PATH_TO_UPLOADS.$file->filename)) {
                if  (!$this->db->delete($this->tablename, array('fileid'=>$file->fileid))) {
                    return False;
                }
            }
        }
        return true;
    }

    /**
     * Create Media object
     * @param $file
     * @return bool|mixed|string
     */
    public function make($file) {

        // First check the file
        $result['error'] = $this->checkUpload($file);
        if ($result['error'] !== true) {
            return $result;
        }

        // Second: Proceed to upload
        $result = $this->upload($file);
        if ($result['error'] !== true) {
            return $result;
        }

        $content = $result['status'];
        $content['date'] = date('Y-m-d h:i:s');

        // Third: add to the Media table
        $content = $this->parsenewdata($content);
        $result['error'] = $this->db->insert($this->tablename,$content);
        if ($result['error'] !== true) {
            $result['error'] = 'SQL: Could not add the file to the media table';
        }

        return $result;
    }

    /**
     * @param array $id
     * @return bool|MediaEntity
     */
    function get(array $id) {
        $file = $this->db->select($this->tablename, array('*'), $id)->single($this->entity);
        if ($file !== false) {
            $this->checkFiles($file);
        }
        return $file;
    }

    /**
     * Check consistency between the media table and the files actually stored on the server
     * @param MediaEntity $file
     * @return bool
     */
    private function checkFiles(MediaEntity $file) {
        // First check if the db points to an existing file
        if (!is_file($this->directory.$file->filename)) {
            // If not, we remove the data from the db
            return $this->delete(array('fileid'=>$file->fileid));
        } else {
            return false;
        }
    }

    /**
     * Validate upload
     * @param $file
     * @return bool|string
     */
    private function checkUpload($file) {
        // Check $_FILES['upfile']['error'] value.
        if ($file['error'][0] != 0) {
            switch ($file['error'][0]) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    return "No file to upload";
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return 'File Exceeds size limit';
                default:
                    return "Unknown error";
            }
        }

        // You should also check file size here.
        if ($file['size'][0] > $this->maxsize) {
            return "File Exceeds size limit";
        }

        // Check extension
        $filename = basename($file['name'][0]);
        $ext = substr($filename, strrpos($filename, '.') + 1);

        if (false === in_array(strtolower($ext),$this->allowed_types)) {
            return "Invalid file type";
        } else {
            return true;
        }
    }

    /**
     * Upload a file
     * @param $file
     * @return mixed
     */
    public function upload($file) {
        $result['status'] = false;
        if (isset($file['tmp_name'][0]) && !empty($file['name'][0])) {
            $result['error'] = $this->checkUpload($file);
            if ($result['error'] === true) {
                $tmp = htmlspecialchars($file['tmp_name'][0]);
                $splitname = explode(".", strtolower($file['name'][0]));
                $type = end($splitname);

                // Create a unique filename
                $fileInfo = $this->makeId($type);
                $fileInfo['type'] = $type;

                // Move file to the upload folder
                $dest = $this->directory.$fileInfo['filename'];
                $results['error'] = move_uploaded_file($tmp,$dest);

                if ($results['error'] == false) {
                    $result['error'] = "Uploading process failed";
                } else {
                    $results['error'] = true;
                    $result['status'] = $fileInfo;
                }
            }
        } else {
            $result['error'] = "No File to upload";
        }
        return $result;
    }

    /**
     * Generate a non-existing random id for the new upload
     * @param string $type: file extension
     * @return array
     */
    public function makeId($type) {
        $rnd = date('Ymd')."_".rand(0,100);
        $newName = $rnd.".".$type;
        while (is_file($this->directory.$newName)) {
            $rnd = date('Ymd')."_".rand(0,100);
            $newName = $rnd.".".$type;
        }
        return array('fileid'=>$rnd, 'filename'=>$newName);
    }
}