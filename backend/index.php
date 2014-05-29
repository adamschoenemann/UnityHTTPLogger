<?php

error_reporting(0);
// Retrieve instance of the framework
$f3=require('lib/base.php');

error_reporting(0);
// Initialize DB
$f3->config("app/config/db.ini");
error_reporting(0);
// Initialize CMS
$f3->config('app/config/config.ini');

error_reporting(0);
// Define routes
$f3->config('app/config/routes.ini');

error_reporting(0);
// Execute application
$f3->run();
