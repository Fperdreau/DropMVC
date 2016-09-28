<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 10/12/2015
 * Time: 16:47
 */

namespace App\Views;


class HomeView {

    public static function index($variables) {
        return require('Home/index.php');
    }

    /**
     * This function display most recently updated tools on the home page
     * @param $tools
     * @return string
     */
    public static function showLast($tools) {
        $content = "";
        foreach ($tools as $tool_name=>$info) {
            $tool_icon = self::makeIcon($tool_name);
            $content .= "
            <div class='tool_container' id='$tool_name'>
                <div class='tool_icon'>$tool_icon</div>
                <div class='tool_name'>$tool_name</div>
                <div class='tool_desc' style='display: none'>
                    <div class='tool_content'>
                        <div class='tool_version'>Version ".$info['version']."</div>
                        <div class='tool_short'>".$info['short']."</div>
                    </div>
                </div>
                <a href='".URL_TO_APP."Tools/$tool_name' class='leanModal' id='modalTool' data-modal='#toolModal' data-section='toolsPop'  data-tool='$tool_name' style='display: none;'></a>
            </div>
            ";
        }
        $result = "
        <div class='tools_container'>
        $content
        </div>
        ";
        return $result;
    }

    public function addBtn($adminopt) {
        return ($adminopt == true) ? "<div class='add_tool buttons pub_btn leanModal' data-section='tools' id='modal_trigger_tool'
        style='width: 100px;'>Add a tool</div>":"";
    }

    public function modBtn($adminopt, $name) {
        return ($adminopt == true) ? "<div class='mod_tool'>
            <a href='tools/index.php?page=tools' class='leanModal' id='modal_trigger_Modtool' data-section='tools' data-tool='$name'>Modify</a>
            </div>" :"";
    }

    /**
     * Display tool information
     * @param object $tool
     * @param bool|false $adminopt
     * @param bool $even
     * @return string
     */
    public static function display($tool, $adminopt=false,$even=false) {
        $even = ($even == false) ? "even":'odd';
        $dlCount = $tool->count['dl'];
        $docCount = $tool->count['doc'];
        $icon = self::makeIcon($tool->name);
        $modBtn = self::modBtn($adminopt,$tool->name);
        $result = "
        <section class='project $even' itemscope='Description of $tool->name' itemtype='http://schema.org/WebApplication'>
            <div itemprop='applicationCategory' style='display: none;'>$tool->applicationCategory</div>
            <div itemprop='copyrightYear' style='display: none;'>$tool->copyright</div>
            <div itemprop='license' style='display: none;'>$tool->license</div>
            <div itemprop='creator' style='display: none;'>Florian Perdreau</div>
            <div itemprop='offers' itemscope itemtype='http://schema.org/Offer' style='display: none;'><span itemprop='price'>0.00</span></div>
            <div id='project_header'>
                <div id='project_icon'>$icon</div>
                <div id='project_info'>
                    <div id='project_title' itemprop='name'>$tool->name</div>
                    <div id='project_version' itemprop='version'>Version: $tool->version</div>
                    $modBtn
                </div>
            </div>

            <div id='project_content'>
                <div id='project_desc' itemprop='description'>$tool->description</div>
                <div id='project_req' itemprop='browserRequirements'><b>Requirements: </b>$tool->requirements</div>
                <div class='tools_btn_div'>
                    <div class='tools_btn left' itemprop='softwareHelp'>
                        <a href='$tool->doc' class='addClick' data-click='view' data-id='$tool->name' target='_blank'>DOCUMENTATION</a>
                    </div>
                    <div class='tools_btn right' itemprop='downloadUrl'>
                        <a href='$tool->dl' class='addClick' data-click='dl' data-id='$tool->name' target='_blank'>DOWNLOAD</a>
                    </div>
                    <div id='project_count'>
                        <div>$docCount views | $dlCount downloads</div>
                    </div>
                </div>
            </div>

        </section>";

        return $result;
    }

    /**
     * This function makes an icon from the uppercase letters present in the tool's name
     * @param string $name: tool's name
     * @return string
     */
    private static function makeIcon($name) {
        $keywords = preg_split("/[\\sa-z]/", $name);
        return implode('',$keywords);
    }

}