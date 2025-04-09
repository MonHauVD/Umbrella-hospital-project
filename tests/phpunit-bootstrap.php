<?php
use PHPMailer\PHPMailer\PHPMailer;

// Path to root directory of app.
define("ROOTPATH", __DIR__."/../api");

// Path to app folder.
define("APPPATH", ROOTPATH."/app");


// Check if SSL enabled.
$ssl = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off" 
     ? true 
     : false;
define("SSL_ENABLED", $ssl);

$app_url = (SSL_ENABLED ? "https" : "http")
         . "://"
         . 'localhost'
        //  . ":8080"
         . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
         . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
define("APPURL", $app_url);

$active_lang = [
    "code" => "en-US",
    "shortcode" => "en",
    "name" => "English",
    "localname" => "English"
];


define("ACTIVE_LANG", $active_lang);
        


require __DIR__ . '/../vendor/autoload.php';
