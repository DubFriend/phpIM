<?php
require ROOT . "lib.php";
require ROOT . "sql.php";
require ROOT . "factory.php";
require ROOT . "router.php";
require ROOT . "base.php";
require ROOT . "manager.php";
require ROOT . "user.php";
require ROOT . "mustache.php-2.3.0/src/Mustache/Autoloader.php";

Mustache_Autoloader::register();

$get = sanitize_array($_GET);
$post = sanitize_array($_POST);
$server = sanitize_array($_SERVER);

$_GET = $_POST = $_SERVER = null;

$Factory = new Factory(array(
    "get" => $get,
    "post" => $post,
    "server" => $server,
    "session" => new Session(),
    "database" => new Sequel(array(
        "connection" => new PDO(
            "mysql:host=" . DATABASE_HOST . ";dbname=" . DATABASE_NAME,
            DATABASE_USER,
            DATABASE_PASS
        )
    ))
));

$Router = new Router(array(
    "factory" => $Factory,
    "path" => $server['PATH_INFO']
));

$Controller = $Router->build_controller();
echo $Controller->respond();
/*
switch(try_array($get, "type")) {
    case "manager":
        $Controller = $Factory->build_manager_controller();
        break;
    case "user":
        $Controller = $Factory->build_user_controller();
        break;
    default:
        throw new Exception("invalid \$_GET['type'] value");
}*/
?>
