<?php
ini_set('display_errors', 1); 
error_reporting(E_STRICT|E_ALL);

require ROOT . "define.php";

switch(DEPLOYMENT) {
    case "development":
        ini_set('display_errors', 1); 
        error_reporting(E_STRICT|E_ALL);
        break;
    case "production":
        ini_set('display_errors', 0);
        error_reporting(0);
        break;
    default:
        throw new Exception("Invalid DEPLOYMENT value");
}

require ROOT . "lib.php";
require ROOT . "sql.php";
require ROOT . 'mustache.php-2.3.0/src/Mustache/Autoloader.php';

Mustache_Autoloader::register();

$get = sanitize_array($_GET);
$post = sanitize_array($_POST);
$server = sanitize_array($_SERVER);

$Session = new Session();
$Database = new Sequel(array("connection" => new PDO(
	"mysql:host=localhost;dbname=phpIM",
	'root',
	'P0l.ar-B3ar'
)));

echo "foo";
?>