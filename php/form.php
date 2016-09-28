<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 22/11/14
 * Time: 12:19
 */

require_once('../inc/boot.php');

if (!empty($_POST['get_app_status'])) {
    echo json_encode($AppCore->status);
    exit;
}

if (!empty($_POST['isLogged'])) {
    $result = (isset($_SESSION['logok']) && $_SESSION['logok']);
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Pages Management
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Get Pages
if (!empty($_POST['getPage'])) {
    $page = htmlspecialchars($_POST['getPage']);
    $Page = new AppPage($db,$page);
    $result = $Page->check_login();
    $result['pageName'] = $Page->filename;
    $result['title'] = $Page->meta_title;
    $result['keywords'] = $Page->meta_keywords;
    $result['description'] = $Page->meta_description;
    echo json_encode($result);
    exit;
}

// Modify page settings
if (!empty($_POST['modPage'])) {
    $name = htmlspecialchars($_POST['name']);
    $Page = new AppPage($db,$name);
    if ($Page->update($_POST)) {
        $result['status'] = true;
        $result['msg'] = "The modification has been made!";
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Common to Plugins/Scheduled tasks
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Install/uninstall cron jobs
if (!empty($_POST['installDep'])) {
    $name = $_POST['installDep'];
    $op = $_POST['op'];
    $type = $_POST['type'];
    $App = ($type == 'plugin') ? new AppPlugins($db):new AppCron($db);
    $thisApp = $App->instantiate($name);
    if ($op == 'install') {
        if ($thisApp->install()) {
            $result['status'] = true;
            $result['msg'] = "$name has been installed!";
        } else {
            $result['status'] = false;
        }
    } elseif ($op == 'uninstall') {
        if ($thisApp->delete()) {
            $result['status'] = true;
            $result['msg'] = "$name has been deleted!";
        } else {
            $result['status'] = false;
        }
    } else {
        $result['msg'] = $thisApp->run();
        $result['status'] = true;
    }
    echo json_encode($result);
    exit;
}

// Get settings
if (!empty($_POST['getOpt'])) {
    $name = htmlspecialchars($_POST['getOpt']);
    $op = htmlspecialchars($_POST['op']);
    $App = ($op == 'plugin') ? new AppPlugins($db):new AppCron($db);
    $thisApp = $App->instantiate($name);
    $thisApp->get();
    $result = $thisApp->displayOpt();
    echo json_encode($result);
    exit;
}

// Modify settings
if (!empty($_POST['modOpt'])) {
    $name = htmlspecialchars($_POST['modOpt']);
    $op = htmlspecialchars($_POST['op']);
    $data = $_POST['data'];
    $App = ($op == 'plugin') ? new AppPlugins($db): new AppCron($db);
    $thisApp = $App->instantiate($name);
    $thisApp->get();
    if ($thisApp->update(array('options'=>$data))) {
        $result['stauts'] = true;
        $result['msg'] = "$name's settings successfully updated!";
    } else {
        $result['stauts'] = true;
    }
    echo json_encode($result);
    exit;
}

// Modify status
if (!empty($_POST['modStatus'])) {
    $name = htmlspecialchars($_POST['modStatus']);
    $status = htmlspecialchars($_POST['status']);
    $op = htmlspecialchars($_POST['op']);
    $App = ($op == 'plugin') ? new AppPlugins($db): new AppCron($db);
    $thisApp = $App->instantiate($name);
    $thisApp->get();
    $thisApp->status = $status;
    if ($thisApp->isInstalled()) {
        $result = $thisApp->update();
    } else {
        $result = False;
    }
    echo json_encode($result);
    exit;
}

if (!empty($_POST['modSettings'])) {
    $name = htmlspecialchars($_POST['modSettings']);
    $option = htmlspecialchars($_POST['option']);
    $value = htmlspecialchars($_POST['value']);
    $op = htmlspecialchars($_POST['op']);

    $App = ($op == 'plugin') ? new AppPlugins($db): new AppCron($db);
    $thisApp = $App->instantiate($name);
    if ($thisApp->isInstalled()) {
        $thisApp->get();
        $thisApp->$option = $value;
        if ($op == 'plugin') {
            $result = $thisApp->update();
        } else {
            $thisApp->time = $App::parseTime($thisApp->dayNb, $thisApp->dayName, $thisApp->hour);
            if ($thisApp->update()) {
                $result = $thisApp->time;
            } else {
                $result = false;
            }
        }
    } else {
        $result = False;
    }
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Scheduled Tasks
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Modify cron job
if (!empty($_POST['mod_cron'])) {
    $cronName = $_POST['cron'];
    $option = $_POST['option'];
    $value = $_POST['value'];
    $CronJobs = new AppCron($db);
    $cron = $CronJobs->instantiate($cronName);
    if ($cron->isInstalled()) {
        $cron->get();
        $cron->$option = $value;
        $cron->time = AppCron::parseTime($cron->dayNb, $cron->dayName, $cron->hour);
        if ($cron->update()) {
            $result = $cron->time;
        } else {
            $result = false;
        }
    } else {
        $result = False;
    }

    echo json_encode($result);
    exit;
}

// Run cron job
if (!empty($_POST['run_cron'])) {
    $cronName = $_POST['cron'];
    $CronJobs = new AppCron($db);
    $cron = $CronJobs->instantiate($cronName);
    $result['msg'] = $cron->run();
    $result['status'] = true;
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Plugins
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['get_plugins'])) {
    $page = $_POST['page'];
    $Plugins = new AppPlugins($db);
    $plugins = $Plugins->getPlugins($page);
    echo json_encode($plugins);
    exit;
}

if (!empty($_POST['mod_plugins'])) {
    $plugin = $_POST['plugin'];
    $option = $_POST['option'];
    $value = $_POST['value'];
    $Plugins = new AppPlugins($db);
    $plugin = $Plugins->instantiate($plugin);
    if ($plugin->installed) {
        $plugin->get();
        $plugin->options[$option] = $value;
        $result = $plugin->update();
    } else {
        $result = False;
    }

    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Process submissions
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */
// Submit a new presentation
if (!empty($_POST['process_pub'])) {
    // check entries
    $pub = new Publication($db);

    $op = htmlspecialchars($_POST['process_pub']);
    if ($op == 'submit') {
        $out = $pub->make($_POST);
    } else {
        $out = $pub->update($_POST);
    }
    $result['status'] = $out;
    $result['msg'] = ($out == true) ? "Operation successful":"Oops, something went wrong";
    echo json_encode($result);
    exit;
}

//  delete presentation
if (!empty($_POST['del_pub'])) {
    $Presentation = new Publication($db);
    $id_Presentation = htmlspecialchars($_POST['del_pub']);
    if ($Presentation->delete($id_Presentation)) {
        $result['msg'] = "The presentation has been deleted!";
        $result['status'] = true;
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}


// Display presentation (modal dialog)
if (!empty($_POST['show_pub'])) {
    $id_Presentation = htmlspecialchars($_POST['show_pub']);
    if ($id_Presentation === "false") {
        $id_Presentation = false;
    }

    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = false;
    }

    $user = new Users($db,$_SESSION['username']);
    $pub = new Publication($db,$id_Presentation);
    $form = $pub->displaypub($user);
    echo json_encode($form);
    exit;
}

// Display submission form
if (!empty($_POST['getpubform'])) {
    $id_Presentation = $_POST['getpubform'];
    if ($id_Presentation == "false") {
        $pub = false;
    } else {
        $pub = new Publication($db,$id_Presentation);
    }
    $result = displayform($pub);
    echo json_encode($result);
    exit;
}

//  delete files
if (!empty($_POST['del_upl'])) {
    $uplname = htmlspecialchars($_POST['uplname']);
    $fileid = explode(".",$uplname);
    $fileid = $fileid[0];
    $up = new Media($db, $fileid);
    $result = $up->delete();
    $result['uplname'] = $fileid;
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Tools
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

// Display Tools form
if (!empty($_POST['gettoolform'])) {
    $toolName = ($_POST['gettoolform'] !== "false") ? htmlspecialchars($_POST['gettoolform']):null;
    $tool = new MyTools($db,$toolName);
    $result = ($_POST['form'] === "true") ? $tool->form($toolName):$tool->display();
    echo json_encode($result);
    exit;
}

// Submit a new presentation
if (!empty($_POST['process_tool'])) {
    // check entries
    $pub = new MyTools($db);
    $op = htmlspecialchars($_POST['process_tool']);
    if ($op == 'submit') {
        $out = $pub->make($_POST);
    } else {
        $out = $pub->update($_POST);
    }
    $result['status'] = $out;
    $result['msg'] = ($out == true) ? "Operation successful":"Oops, something went wrong";
    echo json_encode($result);
    exit;
}

// Count clicks
if (!empty($_POST['addClick'])) {
    $type = $_POST['type'];
    $name = $_POST['name'];
    $visitor = new Visitor($db);
    $content = array('type'=>$type,'name'=>$name);
    $result = $visitor->make($content);

    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
CV
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

// Display Tools form
if (!empty($_POST['getcvform'])) {
    $cvid = ($_POST['getcvform'] !== "false") ? htmlspecialchars($_POST['gettoolform']):null;
    $cv = new Curriculum($db,$cvid);
    $result = $cv->form($cvid);
    echo json_encode($result);
    exit;
}

// Add/modify cv
if (!empty($_POST['process_cv'])) {
    // check entries
    $pub = new Curriculum($db);
    $op = htmlspecialchars($_POST['process_cv']);
    if ($op == 'submit') {
        $result['status'] = $pub->make($_POST);
    } else {
        $result['status'] = $pub->update($_POST);
    }
    echo json_encode($result);
    exit;
}

// Generate PDF version of CV
if (!empty($_POST['make_pdf'])) {
    var_dump(URL_TO_APP);
    require_once("../vendors/dompdf/dompdf_config.inc.php");
    $filename = 'Florian_Perdreau_CV.pdf';
    $relPath = 'uploads/'.$filename;
    $filePath = PATH_TO_APP."/".$relPath;
    $url = $AppCore->site_url.$relPath;
    $content = $_POST['make_pdf'];
    $dompdf = new DOMPDF();
    $dompdf->set_base_path(realpath(PATH_TO_APP.'/css/'));
    $dompdf->load_html($content);
    $dompdf->render();
    //$dompdf->stream($filename);
    //$dompdf->stream($filename,array('Attachment'=>0));
    $output = $dompdf->output();
    file_put_contents(($filePath), $output);
    echo json_encode($url);
    exit;
}

// Remove cv element
if (!empty($_POST['delcvEl'])) {
    $cvid = htmlspecialchars($_POST['delcvEl']);
    $cv = new Curriculum($db,$cvid);
    $result['status'] = $cv->delete();
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Contact
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['contact_send'])) {
    $subject = htmlspecialchars($_POST["subject"]);
    $usr_msg = htmlspecialchars($_POST["message"]);
    $usr_mail = htmlspecialchars($_POST["email"]);
    $usr_name = htmlspecialchars($_POST["name"]);
    $content = "Message sent by $usr_name ($usr_mail):<br><p>$usr_msg</p>";

    // Get admin's email
    $admin = new Users($db);
    $admin->getadmin();

    $AppMail = new Mail($db,$AppConfig);

    // Format email
    $body = $AppMail -> formatmail($content);
    $subject = "Contact from $usr_name: $subject";

    if ($AppMail->send_mail($admin->email,$subject,$body)) {
        $result['status'] = true;
        $result['msg'] = "Your message has been sent!";
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Login/Sign up
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Check login
if (!empty($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $user = new Users($db,$username);
    $result = $user->login($_POST);
    echo json_encode($result);
    exit;
}

// Logout
if (!empty($_POST['logout'])) {
    session_unset();
    session_destroy();
    echo json_encode(true);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Admin tools
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

// Modify admin information
if (!empty($_POST['modify_user'])) {
    $username = htmlspecialchars($_POST['username']);
    $user = new Users($db,$_POST['username']);
    $result = $user->update($_POST);
    echo json_encode($result);
    exit;
}


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Application settings
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Update application settings
if (!empty($_POST['config_modify'])) {
    $object = htmlspecialchars($_POST['object']);
    $instance = new $object($db);
    if ($instance->config->update($_POST)) {
        $result['msg'] = "Modifications have been made!";
        $result['status'] = true;
    } else {
        $result['status'] = false;
    }
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Add news
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Add a new post
if (!empty($_POST['post_add'])) {
    if ($_POST['post_add'] === 'post_add') {
        $post = new Posts($db);
        $result = $post->make($_POST);
    } else {
        $id = htmlspecialchars($_POST['postid']);
        $post = new Posts($db,$id);
        $result = $post->update($_POST);
    }
    echo json_encode($result);
    exit;
}

// Show selected post
if (!empty($_POST['post_show'])) {
    $postid = $_POST['postid'];
    if ($postid == "false") $postid = false;
    $username = htmlspecialchars($_SESSION['username']);
    $user = new Users($db,$username);
    $post = new Posts($db,$postid);
    $result = $post->showpost($user->fullname,$postid);
    echo json_encode($result);
    exit;
}

// Delete a post
if (!empty($_POST['post_del'])) {
    $postid = htmlspecialchars($_POST['postid']);
    $post = new Posts($db,$postid);
    $result = $post->delete($postid);
    echo json_encode($result);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Upload
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['upload'])) {
	$pub = new Publication($db);
	$filename = $pub->uploadpdf($_FILES['file']);
	echo json_encode($filename);
    exit;
}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Tools
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
if (!empty($_POST['getTool'])) {
    $op = $_POST['getTool'];
    $tool = new MyTools($db);
    $tool->$op += 1;
    $result = $tool->update();
    echo json_encode($result);
    exit;
}



