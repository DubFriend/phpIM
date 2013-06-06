<?php
require ROOT . "library.php";
require ROOT . "sql.php";
require ROOT . "factory.php";
require ROOT . "router.php";
require ROOT . "base.php";
require ROOT . "manager.php";
require ROOT . "user.php";
require ROOT . "conversations.php";
require ROOT . "messages.php";
require ROOT . "mustache.php-2.3.0/src/Mustache/Autoloader.php";

//session_start();
//note: session cannot be maintained on long running script (will block concurrent requests)
//session_write_close();

Mustache_Autoloader::register();

$get = sanitize_array($_GET);
$post = sanitize_array($_POST);
$server = sanitize_array($_SERVER);

$_GET = $_POST = $_SERVER = null;//force use of sanitized versions

$Factory = new Factory(array(
    "get" => $get,
    "post" => $post,
    "server" => $server,
    //"session" => new Session(),
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
//This should be the only output in the program (not including debugging)
echo $Controller->respond();
?>
