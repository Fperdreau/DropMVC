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

include('app' . DIRECTORY_SEPARATOR . 'App.class.php');
use \App\App;

App::boot(false);

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Process Installation
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (isset($_POST['operation'])) {
    $operation = htmlspecialchars($_POST['operation']);
    unset($_POST['operation']);
    $result = null;
    switch ($operation) {
        case "db_info":
            // STEP 1: Check database credentials provided by the user
            $result = \Core\Database\MySqlDb::testdb($_POST);
            break;
        case "do_conf":
            // STEP 2: Write database credentials to config.php file
            $result = \Core\Config::createConfig($_POST);
            break;
        case "backup":
            // STEP 3: Do Backups before making any modifications to the db
            if ($_SESSION['op'] === 'true') {
                $App = App::getInstance(false);
                $App->backup();
                $result['msg'] = "Backup is complete!";
            }
            $result['status'] = true;
            break;
        case "install_db":
            try {
                // STEP 4: Configure database
                $op = $_SESSION['op'] === "new";
                $App = App::getInstance(!$op);

                // Install database
                $App->install($op);

                $version = App::$version;
                $_POST['version'] = $version;

                // Load and update application settings
                $App->loadAppSettings();
                $App->config->update($_POST, array());

                // Create Page table
                $AppPage = new \App\Controller\PagesController();
                $AppPage->getPages();

                // Clean Media
                $Media = new \App\Controller\MediaController();
                $Media->clean();

                $result['msg'] = "Database installation complete!";
                $result['status'] = true;
            } catch (Exception $e) {
                echo(json_encode($e));
                exit();
            }
            break;

        case "admin_creation":
            // Final step: create admin account (for new installation only)
            $user = new \App\Controller\UsersController();
            $user->add();
            break;
        default:
            $result = null;
            break;
    }

    if (!is_null($result)) {
        echo json_encode($result);
    }
    exit;
}

/**
 * Get page content
 *
 */
if (isset($_POST['getpagecontent'])) {
    $step = htmlspecialchars($_POST['getpagecontent']);
    $_SESSION['step'] = $step;
    if (!isset($_SESSION['op'])) $_SESSION['op'] = htmlspecialchars($_POST['op']);
    $_SESSION['op'] = $_POST['op'];
    $op = $_POST['op'];
    $new_version = App::$version;

    /**
     * Get configuration from previous installation
     * @var  $config
     *
     */
    $db = \Core\Database\MySqlDb::getInstance();
    $version = $db->getConfig('version');
    $_SESSION['installed_version'] = $version;
    $AppName = App::$app_name;
    if ((int)$step == 0) {
        $title = "Welcome!";
        if ($version == false) {
            $operation = "
                <p>Hello</p>
                <p>It seems that <i>{$AppName}</i> has never been installed here before.</p>
                <p>We are going to start from scratch... but do not worry, it is all automatic. We will guide you through
                 the installation steps and you will only be required to provide us with some information regarding the hosting environment.</p>
                <p>Click on the 'next' button once you are ready to start.</p>
                <p>Thank you for your interest in <i>{$AppName}</i>
                <p style='text-align: center'><input type='button' id='submit' value='Start' class='start' data-op='new'></p>";
        } else {
            $operation = "
                <p>Hello</p>
                <p>The current version of <i>{$AppName}</i> installed here is $version. You are about to install the version $new_version.</p>
                <p>You can choose to either do an entirely new installation by clicking on 'New installation' or to simply update your current version to the new one by clicking on 'Update'.</p>
                <p class='msg_warning'>Please, be aware that choosing to perform a new installation will completely erase all the data present in your <i>{$AppName}</i> database!!</p>
                <p style='text-align: center'>
                <input type='button' id='submit' value='New installation'  class='start' data-op='new'>
                <input type='button' id='submit' value='Update'  class='start' data-op='update'>
                </p>";
        }
    } elseif ($step == 1) {
        $version = App::$version;
        $title = "Step {$step}: Database configuration";
        $form = new \Core\HTML\BootstrapForm($db->config);
        $formContent = $form->input('host',"Host Name", array('type'=>'text'), "required autocomplete='on'");
        $formContent .= $form->input('username',"Username", array('type'=>'text'), "required autocomplete='on'");
        $formContent .= $form->input('passw',"Password", array('type'=>'password'));
        $formContent .= $form->input('dbname',"DB Name", array('type'=>'text'), "required autocomplete='on'");
        $formContent .= $form->input('dbprefix',"DB Prefix", array('type'=>'text'), "required autocomplete='on'");
        $submit = $form->submit('proceed','Next');
        $operation = "
			<form action='install.php' method='post'>
			    <input type='hidden' name='operation' value='db_info'>
                <input type='hidden' name='version' value='$version'>
				{$formContent}
                <div class='submit_btns'>{$submit}</div>
			</form>
			<div class='feedback'></div>
		";
    } elseif ($step == 2) {
        // Website settings
        $settingsController = new \App\Controller\SettingsController();
        $settings = ($op !== 'new') ? $settingsController->get('App'):array();
        $settings['version'] = App::$version;

        $form = new Core\HTML\BootstrapForm($settings, 'div');
        $formContent = $form->input('siteTitle', 'WebSite title', array('type' => 'text'));
        $submit = $form->submit('processform','Next');

        $url_action = URL_TO_APP . 'settings/update/App';
        $version = App::$version;
        $title = "Step {$step}: Choose a name for your website";
        $operation = "
            <form action='{$url_action}' method='post'>
                <input type='hidden' name='operation' value='install_db'>
                {$formContent}
                <div class='submit_btns'>{$submit}</div>
            </form>
        ";

    } elseif ($step == 3) {
        // Mailing system settings
        $settingsController = new \App\Controller\SettingsController();
        $title = "Step {$step}: Mailing system settings";
        $operation = $settingsController->show('Mail');

    } elseif ($step == 4) {
        $title = "Step {$step}: Admin account creation";
        $userController = new \App\Controller\UsersController();
        $operation = $userController->showForm();

    } elseif ($step == 5) {
        $title = "Installation complete!";
        $operation = "
		<p id='success'>Congratulations!</p>
		<p class='msg_warning'> Now you can delete the 'install.php' file from the root folder of the application</p>
		<p style='text-align: right'><input type='submit' name='submit' value='Finish' class='finish' data-url='".URL_TO_APP."admin'></p>";
    }

    $result['content'] = "
		<h2>{$title}</h2>
		<section>
		    <div class='feedback'></div>
			<div id='operation'>{$operation}</div>
		</section>
	";
    $result['step'] = $step;
    $result['op'] = $_POST['op'];
    echo json_encode($result);
    exit;
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <META http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link type='text/css' rel='stylesheet' href="app/Views/templates/default/css/stylesheet.css"/>
    <link type='text/css' rel='stylesheet' href="app/Views/templates/default/css/essentials.css"/>

    <!-- JQuery -->
    <script type="text/javascript" src="public/js/jquery-3.1.0.min.js"></script>
    <script type="text/javascript" src="public/js/form.js"></script>

    <!-- PasswordChecker -->
    <script type="text/javascript" src="public/js/passwordchecker/passwordchecker.js"></script>
    <link type='text/css' rel='stylesheet' href="public/js/passwordchecker/css/style.css">

    <style type="text/css">
        .box {
            background: #FFFFFF;
            width: 60%;
            padding: 20px;
            margin: 2% auto;
            border: 1px solid #eeeeee;
        }

        section {
            box-shadow: none;
        }
    </style>

    <!-- Bunch of jQuery functions -->
    <script type="text/javascript">

        // Get url params ($_GET)
        function getParams() {
            var url = window.location.href;
            var splitted = url.split("?");
            if(splitted.length === 1) {
                return {};
            }
            var paramList = decodeURIComponent(splitted[1]).split('&');
            var params = {};
            for(var i = 0; i < paramList.length; i++) {
                var paramTuple = paramList[i].split("=");
                params[paramTuple[0]] = paramTuple[1];
            }
            return params;
        }

        // Get page content
        function getpagecontent(step,op) {
            var div = $('#pagecontent');

            var callback = function(result) {
                var url = 'install.php?step='+result.step+"&op="+result.op;
                if(url!=window.location){
                    window.history.pushState({path:url},'',url);
                }
                $('#pagecontent').html(result.content).fadeIn(200);
            };
            var data = {getpagecontent: step, op: op};
            processAjax(div,data,callback,'install.php');
        }

        /**
         * Show loading animation
         */
        function loadingDiv(el) {
            el
                .css('position','relative')
                .append("<div class='loadingDiv' style='background: rgba(255, 255, 255, .8); width: 100%; height: 100%;'></div>")
                .show();
        }

        /**
         * Remove loading animation at the end of an AJAX request
         * @param el: DOM element in which we show the animation
         */
        function removeLoading(el) {
            el.fadeIn(200);
            el.find('.loadingDiv')
                .fadeOut(1000)
                .remove();
        }

        function showText(el, text) {
            el.find('.feedbackForm').remove();
            el.append("<div class='feedbackForm'></div>");
            var width = el.width();
            var height = el.height();
            var feedbackForm = el.find('.feedbackForm');
            var msg = "<div class='msg_status'>" + text + "</div>";
            feedbackForm
                .css({width: width+'px', height: height+'px'})
                .html(msg)
                .fadeIn(200);

            setTimeout(function() {
                feedbackForm
                    .fadeOut(200)
                    .remove();
            }, 1000);
        }

        /**
         * Proceed to database installation
         */
        function proceed(operation, data, operationDiv, text, callback) {
            var dataToProcess = (data !== undefined) ? modOperation(data, operation): {operation: operation};
            return {
                url: 'install.php',
                type: 'POST',
                data: dataToProcess,
                beforeSend: function () {
                    showText(operationDiv, text);
                },
                complete: function (data) {
                    var results = data.responseText;
                    validsubmitform(operationDiv, results, callback);
                },
                error: function () {
                    removeLoading(operationDiv);
                }
            };
        }

        /**
         * Find and replace `content` if there
         **/
        function modOperation(data,operation) {
            var i;
            for (i = 0; i < data.length; ++i) {
                if (data[i].name == "operation") {
                    data[i].value = operation;
                    break;
                }
            }
            return data;
        }

        /**
         * Go to next installation step
         **/
        function gonext() {
            step = parseInt(step) + 1;
            getpagecontent(step, op);
            return true;
        }

        /**
         * Test db credentials
         * @param div
         * @param url
         * @param data
         * @param callback
         * @returns {boolean}
         */
        function test_db(div, url, data, callback) {
            loadingDiv(div);
            showText(div, 'Testing connection to database');

            jQuery.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (data) {
                    validsubmitform(div, data, callback, 2000);
                },
                error: function() {
                    removeLoading(div);
                }
            });
            return true;
        }

        /**
         * Process installation step
         * @param input
         * @returns {boolean}
         */
        function process(input) {
            var form = input.length > 0 ? $(input[0].form) : $();
            var operation = form.find('input[name="operation"]').val();
            var data = form.serializeArray();
            var operationDiv = $('#operation');
            var url = form.attr('action');

            // Check form validity
            if (!checkform(form)) return false;

            var callback = function(result) {
                if (result.status) {
                    ajaxManager.run();
                    ajaxManager.add(proceed('do_conf', data, operationDiv, 'Creating configuration file'));
                    ajaxManager.add(proceed('backup', undefined, operationDiv, 'Backup files and database'));
                    ajaxManager.add(proceed('install_db', undefined, operationDiv, 'Installing application', function() {
                        ajaxManager.stop();
                        removeLoading(operationDiv);
                        gonext();
                    })
                    );
                }
            };

            // Check database settings and credentials. If correct, continue installation
            test_db(operationDiv, url, data, callback);
        }

        /**
         * Manage ajax queue
         * @type {{add, remove, run, stop}}
         */
        var ajaxManager = (function() {
            var requests = [];

            return {
                add:  function(opt) {
                    requests.push(opt);
                },
                remove:  function(opt) {
                    if( $.inArray(opt, requests) > -1 )
                        requests.splice($.inArray(opt, requests), 1);
                },
                run: function() {
                    var self = this,
                        oriSuc;

                    if( requests.length ) {
                        oriSuc = requests[0].success;
                        requests[0].success = function() {
                            setTimeout(function() {
                                if( typeof(oriSuc) === 'function' ) oriSuc();
                                requests.shift();
                                self.run.apply(self, []);
                            }, 1000);
                        };

                        $.ajax(requests[0]);

                    } else {
                        self.tid = setTimeout(function() {
                            self.run.apply(self, []);
                        }, 1000);
                    }
                },
                stop:  function() {
                    requests = [];
                    clearTimeout(this.tid);
                }
            };
        }());

        var step = 0;
        var op = 'new';
        $(document).ready(function () {
            $('body')
                .ready(function() {
                    // Get step
                    var params = getParams();
                    step = (params.step == undefined) ? 0:params.step;
                    op = (params.op == undefined) ? false:params.op;
                    getpagecontent(step, op);
                })

                /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
                 Installation/Update
                 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

                // Go to next installation step
                .on('click', '.start', function(e) {
                    e.preventDefault();
                    op = $(this).attr('data-op');
                    getpagecontent(1, op);
                })

                // Go to next installation step
                .on('click', '.finish', function(e) {
                    e.preventDefault();
                    window.location = $(this).data('url');
                })

                .on('click', "button[type='submit']", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!$(this).hasClass('processform')) {
                        process($(this));
                    } else {
                        return false;
                    }
                })

                .on('click',".processform",function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var form = $(this).length > 0 ? $($(this)[0].form) : $();
                    var op = form.find('input[name="op"]').val();
                    var url = form.attr('action');
                    if (!checkform(form)) {return false;}
                    var callback = function(result) {
                        if (result.status == true) {
                            gonext();
                        }
                    };
                    processForm(form,callback,url);
                })


                // Final step: Create admin account
                .on('click','.admin_creation',function(e) {
                    e.preventDefault();
                    var form = $(this).length > 0 ? $($(this)[0].form) : $();
                    var op = form.find('input[name="op"]').val();
                    if (!checkform(form)) {return false;}
                    var callback = function(result) {
                        if (result.status == true) {
                            getpagecontent(6,op);
                        }
                    };
                    processForm(form,callback,'install.php');
                });
        });
    </script>
    <title>Installation</title>
</head>

<body class="mainbody" style="background: #FdFdFd;">

<div id="bodytable">
    <!-- Header section -->
    <div class="box" style='text-align: center; font-size: 1.7em; color: #336699; font-weight: 300;'>
        Installation
    </div>

    <!-- Core section -->
    <div class="box" style="min-height: 300px;">
        <div id="pagecontent"></div>
    </div>

    <!-- Footer section -->
    <div class="box" style="text-align: center">
        <div id="sign" style="margin-top: 20px;">&copy 2014 by Florian Perdreau</div>
    </div>

</div>

</body>

</html>
