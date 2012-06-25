<?php
// Set constants
define('PATH', realpath('.'));
define('ENVIRONMENT', 'test');

// Get app class
require_once(PATH . '/framework/Autoload.php');
require_once('ControllerTestCase.php');
require_once('ModelTestCase.php');

// Initialise autoloader
$autoload = new LiteMVC\Autoload();
$autoload->register();