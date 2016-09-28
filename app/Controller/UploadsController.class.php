<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 23/05/2016
 * Time: 09:19
 */

namespace App\Controller;


use App\Model\MediaModel;

/**
 * Class UploadsController
 * @package App\Controller
 */
class UploadsController extends AppController {

    /**
     * @var MediaModel $Media
     */
    protected $Media;

    /**
     * UploadsController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Media');
    }

    public function index($file) {
        $path = PATH_TO_APP . 'uploads/' . $file;
        if (is_file($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Type: application/force-download");
            header('Content-Disposition: attachment; filename=' . urlencode(basename($file)));
            // header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));

            readfile($path);
            exit;
        } else {
            $this->notFound();
        }
    }

}