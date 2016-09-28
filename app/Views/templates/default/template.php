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

use App\App;
// HTML Start
require('modal.php'); // Import modal section

echo "

<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
        <meta name='viewport' content='width=device-width, target-densitydpi=device-dpi, initial-scale=1.0, user-scalable=yes'/>
        <meta name='description' content='".$page->meta_description."'/>
        <meta name='keywords' content='".$page->meta_keywords."'/>
        <link rel='shortcut icon' type='image/png' href='" . PATH_TO_IMG . "favicon.png'/>
        
        <!--[if lt IE 9]>
        <script src='http://html5shiv.googlecode.com/svn/trunk/html5.js'></script>
        <![endif]-->

        <!-- Hreflang tags -->
        {$hreflang}

        <title>".$page->meta_title."</title>
    </head>

    <body class='mainbody'>
        ";

include_once(PATH_TO_INCLUDES . "analyticstracking.php");
        echo "
        {$modal}
        {$cookie_consent}
        <div class='sideMenu' style='display:none;'>{$this->menu}</div>

        <!-- header -->
        <header>
            <div class='wrap' id='header_content'>
                <div id='sitelogo'>
                    <div id='logo'><img src='" . PATH_TO_IMG . "weblogo.png' alt='logo'></div>
                    <div id='sitetitle'>
                        <div>
                            <div id='titleName'>" . $siteTitle . "</div>
                            <div id='subtitle'>" . $siteSubTitle . "</div>
                        </div>
                    </div>
                </div>

                <div class='menu'></div>

                <div class='top_nav'>
                    {$this->menu}
                </div>
            </div>
            
            <div class='justified_container menu_extra wrap'>
                <div id='logout'>{$logout}</div>
                <!--<div id='languages'>
                {$this->languagesMenu}
                </div>-->   
            </div>

            <!-- Sub-menu -->
            <div class='wrap' id='sub_menu'>
                <!-- Sub menu -->
                <div id='submenu_container'>
                    {$this->submenu}
                </div>
            </div>

        </header>
        <!-- End of header -->

        <!-- Core section -->
        <main>
            <div id='navBar'>{$navBar}</div>
            <div id='pagecontent'>
                <!-- -->{$pagecontent}
            </div>
        </main>
        <!-- End of Main -->

        <!-- Footer section -->
        <footer>
            <div class='wrap' id='footer_content'>

                <div class='justified_container'>

                    <!-- Information/Policies -->
                    {$policies}

                    <!-- Social networks -->
                    <div id='social_networks'>
                        <div class='icons_box'>
                            {$socialNet}
                        </div>
                    </div>
                </div>

                <div id='sign'>" . App::$author . " &copy; ". App::$copyright . " - Version " . App::$version . "</div>
            </div>
        </footer>
        <!-- End of Footer -->
        
                
        <!-- DO NOT MODIFY THE LINES BELLOW -->
        <!-- CSS -->
        <link type='text/css' rel='stylesheet' href='".PATH_TO_CSS."essentials.min.css' />
        <link type='text/css' rel='stylesheet' href='".PATH_TO_CSS."stylesheet.min.css' />
        <link type='text/css' rel='stylesheet' href='".PATH_TO_CSS."modal_style.min.css' />
        <link type='text/css' rel='stylesheet' href='".PATH_TO_CSS."uploader.min.css' />
        <link type='text/css' rel='stylesheet' href='".PATH_TO_JS."passwordchecker/css/style.min.css' />
        
        <!-- Javascript/JQuery -->
        <script type='text/javascript' src='".PATH_TO_JS."jquery-1.11.3.min.js'></script>
        <script type='text/javascript' src='".PATH_TO_JS."loading.min.js'></script>
        
        <!-- Bunch of jQuery functions -->
        <script type='text/javascript' src='".PATH_TO_JS."index.min.js'></script>
        
        <!-- LeanModal -->
        <script type='text/javascript' src='".PATH_TO_JS."jquery.leanModal.min.js'></script>
        
        <!-- mini upload form plugin -->
        <script type='text/javascript' src='".PATH_TO_JS."plugins.min.js'></script>
        <script type='text/javascript' src='".PATH_TO_JS."Myupload.min.js'></script>
        <script type='text/javascript' src='".PATH_TO_JS."form.min.js'></script>
        <script type='text/javascript' src='".PATH_TO_JS."passwordchecker/passwordchecker.min.js'></script>
        <!--<script type='text/javascript' src='".PATH_TO_JS."microdata-tool-master/jquery.microdata.js'></script>-->
        
        <!-- TinyMce (Rich-text textarea) -->
        <!--<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>-->
        <script type='text/javascript' src='".PATH_TO_JS."tinymce/tinymce.min.js'></script>
        
	</body>
</html>
";
