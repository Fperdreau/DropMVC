<?php
/**
 * Database table schema
 * Used by install.php
 *
 * usage: $tables = array(
    "table_name_without_prefix"=>array(
 *      "column_name"=>array("DATA_TYPE", default_value
 *  )
 * );
 */

return array(
    "settings"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "object"=>array("CHAR(50)", false),
        "variable" => array("CHAR(50)", false),
        "value" => array("TEXT", false),
        "primary" => "id"),

    "tasks" => array(
        "id"=>array('INT NOT NULL AUTO_INCREMENT',false),
        "name"=>array('CHAR(20)',false),
        "time"=>array('DATETIME', false),
        "frequency"=>array('CHAR(15)',false),
        "status"=>array('CHAR(3)',false),
        "options"=>array('TEXT',false),
        "description"=>array('TEXT',false),
        "primary"=>"id"
    ),

    "pages"=>array(
        "id"=>array('INT NOT NULL AUTO_INCREMENT',false),
        "name"=>array('CHAR(20)',false),
        "filename"=>array('CHAR(255)',false),
        "parent"=>array('CHAR(20)',false),
        "status"=>array('INT(2)',false),
        "rank"=>array('INT(2)',false),
        "show_menu"=>array('INT(1)',false),
        "meta_title"=>array('VARCHAR(255)',false),
        "meta_keywords"=>array('TEXT(1000)',false),
        "meta_description"=>array('TEXT(1000)',false),
        "primary"=>"id"),

    "pages_i18n"=>array(
        "id"=>array('INT NOT NULL AUTO_INCREMENT',false),
        "name"=>array('CHAR(20)',false),
        "lang"=>array('CHAR(5)',false),
        "meta_title"=>array('VARCHAR(255)',false),
        "meta_keywords"=>array('TEXT(1000)',false),
        "meta_description"=>array('TEXT(1000)',false),
        "primary"=>"id"),

    "media"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "date" => array('DATETIME', false),
        "fileid" => array('CHAR(20)', false),
        "filename" => array('CHAR(255)', false),
        "refid" => array('CHAR(20)', false),
        "type" => array('CHAR(5)', false),
        "primary" => 'id'),

    "blog"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "postid" => array("CHAR(30) NOT NULL"),
        "date" => array("DATETIME", false),
        "title" => array("VARCHAR(255) NOT NULL"),
        "clean_url" => array("TEXT", false),
        "content" => array("TEXT(5000) NOT NULL", false, "post"),
        "username" => array("CHAR(30) NOT NULL", false),
        "homepage" => array("INT(1) NOT NULL", 0),
        "primary" => "id"),

    "blog_i18n"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "postid" => array("CHAR(30) NOT NULL"),
        "lang"=>array('CHAR(5)',false),
        "title" => array("VARCHAR(255) NOT NULL"),
        "clean_url" => array("TEXT", false),
        "content" => array("TEXT(5000) NOT NULL", false, "post"),
        "primary" => "id"),

    "tools"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "created" => array("DATETIME", false),
        "updated" => array("DATETIME", false),
        "name" => array("CHAR(255)", false),
        "clean_url" => array("CHAR(255)", false),
        "version" => array("CHAR(15)", false),
        "description" => array("TEXT(5000)",false),
        "short" => array("TEXT(500)",false),
        "requirements" => array("TEXT(1000)",false),
        "doc" => array("CHAR(255)",false),
        "dl" => array("CHAR(255)",false),
        "copyright" => array("CHAR(9)",false),
        "applicationCategory" => array("CHAR(100)",false),
        "license" => array("CHAR(255)",false),
        "primary" => "id"),

    "plugins"=>array(
        "id"=>array("INT NOT NULL AUTO_INCREMENT",false),
        "name"=>array("CHAR(20)",false),
        "version"=>array("CHAR(5)",false),
        "page"=>array("CHAR(20)",false),
        "status"=>array("CHAR(3)",false),
        "options"=>array("TEXT",false),
        "primary"=>'id'),

    "cv"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "created" => array("DATETIME", false),
        "updated" => array("DATETIME", false),
        "cvid"=>array("CHAR(15)",false),
        "category" => array("CHAR(50)", false),
        "type" => array("CHAR(50)", false),
        "year_from" => array("INT(4)",false),
        "year_to" => array("INT(4)", false),
        "institution" => array("CHAR(50)", false),
        "location" => array("CHAR(50)", false),
        "source" => array("CHAR(50)", false),
        "title" => array("VARCHAR(500)", false),
        "mention" => array("CHAR(100)", false),
        "supervisor" => array("CHAR(100)",false),
        "description" => array("TEXT(1000)", false),
        "primary" => "id"),

    "newsletter"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "email" => array("CHAR(255)",false),
        "primary" => "id"
    ),

    "publications"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "created" => array("DATETIME", false),
        "updated" => array("DATETIME", false),
        "id_pres" => array("CHAR(50)", false),
        "clean_url" => array("TEXT", false),
        "type" => array("CHAR(50)", false),
        "publitype" => array("CHAR(50)",false),
        "authors" => array("TEXT", false),
        "year" => array("INT(4)", false),
        "month" => array("CHAR(10)", false),
        "title" => array("TEXT(500)", false),
        "journal" => array("VARCHAR(500)", false),
        "summary" => array("TEXT(5000)",false),
        "volume" => array("CHAR(50)", false),
        "issue" => array("CHAR(50)", false),
        "pages_from" => array("INT", false),
        "pages_to" => array("INT", false),
        "DOI" => array("CHAR(255)", false),
        "city" => array("CHAR(255)", false),
        "country" => array("CHAR(255)", false),
        "editors" => array("CHAR(255)", false),
        "publishers" => array("CHAR(255)", false),
        "weblink" => array("VARCHAR(500)", false),
        "primary" => "id"),
    
    "socialnet"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "username" => array("CHAR(255)", false),
        "name" => array("CHAR(255)", false),
        "profile" => array("CHAR(255)", false),
        "primary" => "id"),

    "users"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "username" => array("CHAR(50)", false),
        "password" => array("CHAR(255)", false),
        "firstname" => array("CHAR(50)", false),
        "lastname" => array("CHAR(50)", false),
        "fullname" => array("CHAR(50)", false),
        "birthday" => array("DATE", false),
        "nationality" => array("CHAR(50)", false),
        "description" => array("TEXT(5000)", false),
        "position" => array("CHAR(255)", false),
        "title" => array("CHAR(10)", false),
        "photo" => array("CHAR(255)", false),
        "email" => array("CHAR(255)", false),
        "address" => array("CHAR(255)", false),
        "city" => array("CHAR(50)",false),
        "country" => array("CHAR(50)", false),
        "postcode" => array("CHAR(10)", false),
        "office" => array("CHAR(10)", false),
        "mapurl"=>array("TEXT",false),
        "university" => array("CHAR(255)", false),
        "department" => array("CHAR(255)", false),
        "status" => array("CHAR(10)", false),
        "hash" => array("CHAR(50)",false),
        "active" => array("INT(1)", 0),
        "attempt" => array("INT(1)", 0),
        "last_login" => array("DATETIME NOT NULL", false),
        "primary" => "id"
    ),

    "users_i18n"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "username" => array("CHAR(50)", false),
        "lang" => array("CHAR(5)", false),
        "description" => array("TEXT(5000)", false),
        "primary" => "id"
    ),

    "visitor"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "date" => array("DATETIME",false),
        "ip" => array("CHAR(20)", false),
        "name" => array("CHAR(50)", false),
        "type" => array("CHAR(50)",false),
        "city" => array("CHAR(255)",false),
        "country" => array("CHAR(255)",false),
        "primary" => "id"
    ),

    "links"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "name" => array("CHAR(255)", false),
        "clean_url" => array("CHAR(255)", false),
        "url" => array("TEXT",false),
        "primary" => "id"
    ),

    "mail"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "date" => array("DATETIME", false),
        "mail_id" => array("CHAR(15)", false),
        "status" => array("INT(1)", 0),
        "recipients" => array("TEXT NOT NULL", false),
        "attachments" => array("TEXT NOT NULL", false),
        "content" => array("TEXT NOT NULL"),
        "subject" => array("TEXT(500)", false),
        "primary" => "id"
    ),

    "policy"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "policyid" => array("CHAR(30) NOT NULL"),
        "name" => array("CHAR(255) NOT NULL"),
        "content" => array("TEXT", False),
        "clean_url" => array("TEXT", false),
        "primary" => "id"),

    "policy_i18n"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "policyid" => array("CHAR(30) NOT NULL"),
        "name" => array("CHAR(255) NOT NULL"),
        "lang"=>array('CHAR(5)',false),
        "content" => array("TEXT NOT NULL", false, "post"),
        "clean_url" => array("TEXT", false),
        "primary" => "id"),

    "sitemap"=>array(
        "id" => array("INT NOT NULL AUTO_INCREMENT", false),
        "date" => array("DATETIME", false),
        "name" => array("TEXT", false),
        "path" => array("TEXT", false),
        "frequency" => array("FLOAT(1)", 0),
        "primary" => "id"
    )
);