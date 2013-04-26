<?php
function try_array(array $array, $key, $default = null) {
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

function debug($message) {
    if(IS_DEBUG_MESSAGES_ON) {
        $output = "";
        switch(DEBUG_OUTPUT_TYPE) {
            case "command_line":
                $output = "\n\tDEBUG : $message\n";
                break;
            case "html":
                $output = "<p><b style='color=red;'>DEBUG : </b>$message</p>";
                break;
            default:
                throw new Exception("invalid DEBUG_OUTPUT_TYPE value");
        }
        echo $output;
    }
}
?>