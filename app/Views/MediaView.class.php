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

namespace App\Views;


use App\Entity\MediaEntity;
use App\Model\MediaModel;

/**
 * Class MediaView
 * @package App\Views
 */
class MediaView {

    /**
     * @param \Core\HTML\Form $form
     * @return string
     */
    public function settings($form) {
        $formContent = "<div class='submit_btns'>" . $form->submit('processform') . "</div>";
        $formContent .= $form->input('upl_types','Allowed file types',['type'=>'text']);
        $formContent .= $form->input('upl_maxsize','Max file size (in Kb)',['type'=>'text']);
        return $formContent;
    }

    /**
     * Renders uploaded element in uploader
     * @param $data
     * @param $deleteURL
     * @return string
     */
    public static function uplElement($data, $deleteURL) {
        if (in_array($data->type, MediaModel::$types['images'])) {
            $fileContent = "<img src='" . URL_TO_UPLOADS . $data->filename . "'>";
        } else {
            $fileContent = $data->fileid;
        }
        $del = $deleteURL . $data->fileid;
        $url = URL_TO_APP . 'media/' . $data->filename;
        return
            "<div class='upl_info' id='upl_$data->fileid'>
                <a href='{$url}' target='_blank'><div class='upl_name' id='$data->filename'>$fileContent</div></a>
                <div><a href='{$del}'><div class='del_upl' id='$data->fileid' data-upl='$data->fileid'></div></a></div>
            </div>";
    }

    /**
     * Create drag&drop field
     * @param array $links
     * @param string $upl_types
     * @param null|string $formURL
     * @param null $deleteURL
     * @return string
     */
    public static function uploader($links=null, $upl_types=null, $formURL=null, $deleteURL=null) {
        $formURL = (!is_null($formURL)) ? $formURL : URL_TO_APP . 'media/upload';
        $deleteURL = (!is_null($deleteURL)) ? URL_TO_APP . $deleteURL : URL_TO_APP.'media/delete/';

        // Get files associated to this publication
        $filesList = "";
        if (!is_null($links)) {
            foreach ($links as $fileid=>$info) {
                if ($info !== false) {
                    $filesList .= self::uplElement($info, $deleteURL);
                }
            }
        }

        $result = "
        <div class='upl_container'>
    	   <div class='upl_form'>
                <form method='post' action='{$formURL}' enctype='multipart/form-data'>
                    <input type='file' name='upl' class='upl_input' multiple style='display: none;' />
                    <div class='upl_header'>
                        <div class='upl_btn'>
                            Select files ($upl_types)
                        </div>
                        <div class='upl_errors'></div>
                    </div>
                </form>
    	   </div>
            <div class='upl_filelist'>
                <div class='upl_filelist_text'>Drag & Drop</div>
                $filesList
            </div>
        </div>";
        return $result;
    }

    public static function browser(array $data) {
        $content = "";
        foreach($data as $key=>$file) {
            $del_url = URL_TO_APP . "media/delete/";
            $content .= self::browser_Element($file, $del_url);
        }

        return "
        <section>
            <h1>My Uploads</h1>
            <div class='browser_container'>
                <div class='browser_header'>
                </div>
                <div class='browser_fileList'>
                    {$content}
                </div>
            </div>
        </section>
        ";
    }

    public static function browser_Element(MediaEntity $data, $deleteURL) {
        if (in_array($data->type, MediaModel::$types['images'])) {
            $fileContent = "<img src='".URL_TO_UPLOADS.$data->filename."'>";
        } else {

            $fileContent = $data->fileid;
        }
        $del = $deleteURL.$data->fileid;
        $url = URL_TO_UPLOADS.$data->filename;
        return
            "<div class='browser_file_container el_to_del' id='$data->fileid'>
                <div class='browser_file_preview' id='$data->fileid'><a href='{$url}' target='_blank'>$fileContent</a></div>
                <a href='{$del}'><div class='browser_file_delete delete leanModal' data-section='delete_confirmation' data-url='{$del}' data-id='{$data->fileid}'></div></a>
                <div class='browser_file_name'>
                    {$data->filename}
                </div>
            </div>";
    }

}