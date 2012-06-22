<?php
// Set constants
define('PATH', realpath('..'));
define('ENVIRONMENT', 'development');

// Get app class
require_once(PATH . '/LiteMVC/framework/Autoload.php');

// Initialise autoloader
$autoload = new LiteMVC\Autoload();
$autoload->register();