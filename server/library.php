<?php
class Session {

    function __construct() {
        session_start();
    }
    
    function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    function destroy() {
        if($_SESSION) {
            $_SESSION = array();
            if(session_id() != ""  ||  isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 2592000, '/');
            }
            session_unset();
            session_destroy();
        }
    }

    function regenerate() {
        session_regenerate_id();
    }
}

class Clock {
    function time() {
        return time();
    }

    function sleep($microseconds) {
        usleep($microseconds);
    }
}


function try_array(array $array, $key, $default = null) {
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

//will return a string with $trail removed, if $trail is on the end of the string
function remove_trailing($string, $trail) {
    $trailLength = strlen($trail);
    if(substr($string, -$trailLength) === $trail) {
        return substr($string, 0, strlen($string) - $trailLength);
    }
    else return $string;
}


function debug($message) {
    if(IS_DEBUG_MESSAGES_ON) {
        $output = "";
        switch(DEBUG_OUTPUT_TYPE) {
            case "command_line":
                $output = "\n\tDEBUG : $message\n";
                break;
            case "html":
                $output = "<p><b style='color:red;'>DEBUG : </b>$message</p>";
                break;
            default:
                throw new Exception("invalid DEBUG_OUTPUT_TYPE value");
        }
        echo $output;
    }
}


//input:  string
//output: sanitized string, safer to use within the application
//        NOTE: not prepped for database insertion!
function sanitize ($var) {
    $var = strip_tags($var);
    $var = stripcslashes($var);
    return $var;
}

function sanitize_array ($array) {
    $cleanArray = array();
    if(is_array($array)) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $cleanArray[$key] = sanitize_array($value);
            }
            else {
                $cleanArray[$key] = sanitize($value);
            }
        }
    }
    return $cleanArray;
}

//for use in random salt generation.
function random_string($length) {
    $characters = "0123456789abcdef";
    $size = strlen($characters)-1;
    $string = "";     
    for ($p = 0; $p < $length ; $p++) {
        $string .= $characters[mt_rand(0, $size)];
    }
    return $string;
}
?>
