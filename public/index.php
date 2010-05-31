<?php
// Set constants
define('PATH', realpath('../'));
define('ENVIRONMENT', getenv('ENVIRONMENT'));

// Get app class
require_once('../framework/App.php');

// Run application
$app = new LiteMVC\App();
$app->init('config.ini');