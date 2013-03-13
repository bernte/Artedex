<?php

set_include_path(dirname(__FILE__) . '/vendor' . PATH_SEPARATOR . get_include_path());

require_once 'Slim/Slim.php';
require_once 'RedBean/rb.php';

// Register Slim's autoloader
\Slim\Slim::registerAutoloader();

// set up database connection
R::setup('mysql:host=localhost;dbname=artedex','root','');
R::freeze(true);

// Register non-Slim autoloader
function customAutoLoader($class)
{
    $file = rtrim(dirname(__FILE__), '/') . '/' . $class . '.php';
    if ( file_exists($file) ) {
        require $file;
    } else {
        return;
    }
}
spl_autoload_register('customAutoLoader');

// Create an instance of Slim
$app = new \Slim\Slim();

class ResourceNotFoundException extends Exception {}

// include API modules
include 'books.php';
include 'users.php';
include 'debug.php';

$app->run();
