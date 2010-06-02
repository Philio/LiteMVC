<?php
// Set constants
use LiteMVC\App;
define('PATH', realpath('../'));
define('ENVIRONMENT', getenv('ENVIRONMENT') ? getenv('ENVIRONMENT') : 'development');

// Get app class
require_once('../framework/App.php');

// Run application
$app = new LiteMVC\App();
$app->init('config.ini');


echo '<pre>';
print_r($app);