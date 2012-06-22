<?php
// Set constants
define('PATH', realpath('../'));
define('ENVIRONMENT', getenv('ENVIRONMENT') ? getenv('ENVIRONMENT') : 'development');

// Get app class
require_once(PATH . '/framework/Autoload.php');

// Run application
$app = new LiteMVC\App();
$app->init('config.ini')->run();
