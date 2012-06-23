<?php
// Set constants
define('PATH', realpath('.'));
define('ENVIRONMENT', 'test');

// Get app class
require_once(PATH . '/framework/Autoload.php');

// Initialise autoloader
$autoload = new LiteMVC\Autoload();
$autoload->register();