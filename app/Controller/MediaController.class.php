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

namespace App\Controller;


use App\App;
use App\Model\MediaModel;
use App\Views\AuthView;
use App\Views\MediaView;
use finfo;

/**
 * Class MediaController
 * @package App\Controller
 */
class MediaController extends AppController {

    /**
     * @var MediaModel
     */
    protected $Media;

    /**
     * @var MediaView
     */
    protected $view;

    /**
     * MediaController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Media');
        $this->loadView('Media');
    }

    /**
     * Renders file
     * @param $file
     */
    public function index($file) {
        $data = $this->Media->get(array('filename'=>$file));
        if ($data == false) {
            $this->notFound();
        } else {
            $title = $data->title;
            VisitorController::addClick($file, 'dl');
            $path = PATH_TO_APP . 'uploads/' . $data->filename;
            $finfo = new finfo(FILEINFO_MIME);
            $info = $finfo->file($path);
            $split = explode(';', $info);
            if ($split[0] === 'application/pdf') {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header("Content-Type: application/force-download");
                header('Content-Disposition: attachment; filename=' . urlencode(basename($file)));
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($path));
                readfile($path);
            } else {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header("Content-Type: application/force-download");
                header('Content-Disposition: attachment; filename=' . urlencode(basename($file)));
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($path));
                readfile($path);
            }
        }
        exit;
    }

    /**
     * Render page
     * @param string $view
     * @param array $variables
     * @param bool $force
     * @param array $page_info
     * @return bool|void
     */
    public function render($view, $variables=[], $force=false, array $page_info=null) {
        $_SESSION['current_url'] = URL_TO_APP . $_GET['url'];

        // Get page information (meta-data, access requirements)
        $page = $this->loadPage($page_info);

        // Check access permission
        $allowed = self::checkAccess($page->status);

        // Application status
        $AppStatus = App::getInstance()->getSettings('status');

        // Start buffering
        if (!empty($variables)) {
            extract($variables);
        }

        if ($force || $AppStatus == 1 || $page->parent === 'admin' || $allowed['user_status'] === 'admin' ) {
            if ($allowed['status'] || $force) {
                $view = str_replace('.', DS, $view);
                require($this->viewPath . $view . '.php');
            } else {
                if ($allowed['user_status'] === 'not_allowed') {
                    $this->forbidden();
                } else {
                    echo AuthView::login();
                    $allowed['status'] = true;
                }
            }
        } else {
            require($this->viewPath . 'error/work.php');
        }

        return true;
    }

    /**
     * Add file to database
     * @param $files
     * @return bool|mixed|string
     */
    public function add($files) {
        return $this->Media->make($files);
    }

    /**
     * Upload files and add them to the database
     */
    public function upload() {
        $result = $this->Media->make($_FILES['file']);
        $result['name'] = false;
        if ($result['error'] === true) {
            $name = explode('.',$result['status']['filename']);
            $name = $name[0];
            $result['name'] = $name;
            $result['content'] = $this->view->uplElement($this->Media->get(array('fileid'=>$name)),
                URL_TO_APP.'Media/delete/');

            // Compress image
            self::compressImage(PATH_TO_UPLOADS . $result['status']['filename'], PATH_TO_UPLOADS . $result['status']['filename'], 75);
        }
        echo json_encode($result);
    }

    /**
     * Compress images
     * @param string $source_url: path to source
     * @param string $destination_url: path to destination
     * @param int $quality: image quality (0: smallest file, 100: biggest file)
     * @return string: destination url
     */
    private static function compressImage($source_url, $destination_url, $quality) {
        $info = getimagesize($source_url);

        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source_url);
        elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source_url);
        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source_url);
        else return $destination_url;

        //save file
        imagejpeg($image, $destination_url, $quality);

        //return destination file
        return $destination_url;
    }


    /**
     * Create image thumbs
     * @param string $image_src: source file
     * @param null|string $image_dest: destination folder
     * @param int $max_size: maximum image resolution
     * @param bool|FALSE $expand
     * @param bool|FALSE $square
     * @return bool
     */
    static function imagethumb( $image_src , $image_dest = NULL , $max_size = 100, $expand = FALSE, $square = FALSE ) 	{
        if( !file_exists($image_src) ) return FALSE;

        // Get image info
        $fileinfo = getimagesize($image_src);
        if( !$fileinfo ) return FALSE;

        $width     = $fileinfo[0];
        $height    = $fileinfo[1];
        $type_mime = $fileinfo['mime'];
        $type      = str_replace('image/', '', $type_mime);

        if( !$expand && max($width, $height)<=$max_size && (!$square || ($square && $width==$height) ) ) {
            // image is smaller than max size
            if($image_dest)	{
                return copy($image_src, $image_dest);
            } else {
                header('Content-Type: '. $type_mime);
                return (boolean) readfile($image_src);
            }
        }

        // Compute new dimensions
        $ratio = $width / $height;
        if( $square )	{
            $new_width = $new_height = $max_size;
            if( $ratio > 1 ) {
                // Landscape
                $src_y = 0;
                $src_x = round( ($width - $height) / 2 );

                $src_w = $src_h = $height;
            } else {
                // Portrait
                $src_x = 0;
                $src_y = round( ($height - $width) / 2 );

                $src_w = $src_h = $width;
            }
        } else {
            $src_x = $src_y = 0;
            $src_w = $width;
            $src_h = $height;

            if ( $ratio > 1 ) {
                // Landscape
                $new_width  = $max_size;
                $new_height = round( $max_size / $ratio );
            } else {
                // Portrait
                $new_height = $max_size;
                $new_width  = round( $max_size * $ratio );
            }
        }

        // Create new image from the original
        $func = 'imagecreatefrom' . $type;
        if( !function_exists($func) ) return FALSE;

        $image_src = $func($image_src);
        $new_image = imagecreatetruecolor($new_width,$new_height);

        // Transparency for PNG
        if( $type=='png' )	{
            imagealphablending($new_image,false);
            if( function_exists('imagesavealpha') )
                imagesavealpha($new_image,true);

            // Transparency for GIF
        } elseif( $type=='gif' && imagecolortransparent($image_src)>=0 ) {
            $transparent_index = imagecolortransparent($image_src);
            $transparent_color = imagecolorsforindex($image_src, $transparent_index);
            $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
            imagefill($new_image, 0, 0, $transparent_index);
            imagecolortransparent($new_image, $transparent_index);
        }

        // Resize image
        imagecopyresampled(
            $new_image, $image_src,
            0, 0, $src_x, $src_y,
            $new_width, $new_height, $src_w, $src_h
        );

        // Save image
        $func = 'image'. $type;
        if($image_dest)	{
            $func($new_image, $image_dest);
        }

        // Free memory
        imagedestroy($new_image);
        return true;
    }

    /**
     * Get file information
     * @param $id
     * @return mixed
     */
    public function get($id) {
        return $this->Media->get(array('fileid'=>$id));
    }

    /**
     * Return all files associated with an id
     * @param $id
     * @return mixed
     */
    public function all($id) {
        return $this->Media->all(array('refid'=>$id));
    }

    /**
     * Delete all files associated with an unique id
     * @param $id
     * @return bool
     */
    public function delete_all($id) {
        $result['status'] = $this->Media->delete_all(array('refid'=>$id));

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
            return true;
        } else {
            return true;
        }
    }

    /**
     * Clean database
     */
    public function clean() {
        $this->Media->clean();
    }

    /**
     * Delete a file
     * @param $id
     * @return bool
     */
    public function delete($id) {
        $result['status'] = $this->Media->delete(array('fileid'=>$id));

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
            return true;
        } else {
            return true;
        }
    }

    /**
     * Render uploader
     * @param array $links
     * @param string|null $formURL
     * @param string|null $deleteURL
     * @return string
     */
    public function uploader($links, $formURL=null, $deleteURL=null) {
        $upl_types = $this->Media->settings['upl_types'];
        $this->loadView('Media');
        return $this->view->uploader($links, $upl_types, $formURL, $deleteURL);
    }

    /**
     * Browse media files
     * @return bool
     */
    public function browser()  {
        $data = $this->Media->all(array());
        $content = $this->view->browser($data);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($content);
            return true;
        } else {
            $this->render('admin.index', compact('content'));
            return true;
        }
    }

}