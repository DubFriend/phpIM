<?php
/*
class Router_Parser {
    
    function parse($path) {
        $curlyStack = $squareStack = array();



        return explode("/", $path);
    }
}
*/

class Router_Exception extends Exception {}

class Router {
    private $Factory,
            $path,
            $stringPath;

    function __construct(array $fig = array()) {
        $this->Factory = $fig['factory'];
        //lowercase and remove begginning and trailing slashes
        $this->stringPath = strtolower(substr(remove_trailing($fig['path'], '/'), 1));

        $path = $this->parse_route($this->stringPath);

        $this->path = $path;
    }


    //tokenize on '/' character excepting json sections (checks for {} and [])
    private function parse_route($path) {
/*
        if(strlen($path) > 0) {
            $stack = $tokens = array();
            $lastTokenIndex = 0;

            //find sections of json, remove it and store, and replace with an indexed marker
            $firstSquare = strpos($path, "[");
            $firstCurly = strpos($path, "{");
            
            if($firstSquare !== false and $firstCurly !== false) {}
            else if($firstSquare !== false) {}
            else if($firstCurly !== false) {}
            else {}

            return array_merge(
                $tokens,
                $this->parse_route(substr($path, $lastTokenIndex))
            );
        }
        else {
            //recursion termination condition
            return array();
        }
        //parse remaining string normally
        //insert removed json sections into the array.
*/
        return explode("/", $path);
    }

    function build_controller() {
        debug(print_r($this->path, true));
        $Controller = null;
        switch($this->path[0]) {
            case "user_chat":
                $Controller = $this->Factory->build_user_controller();
                break;
            case "manager":
                $Controller = $this->Factory->build_manager_controller();
                break;
            case "conversations":
                $Controller = $this->follow_conversations_path();
                break;
            default:
                throw new Router_Exception("invalid base level path");	
        }
        return $Controller;
    }

    private function follow_conversations_path() {
        $Controller = null;
        switch(try_array($this->path, 1)) {
            case null:
                $Controller = $this->Factory->build_new_conversations_controller();
                break;
            case "live":
                $Controller = $this->Factory->build_live_conversations_controller();
                break;
            case "updates":
                $Controller = $this->follow_conversations_updates_path();
                break;
            case "messages":
                $Controller = $this->Factory->build_messages_controller();
                break;
            default:
                throw new Router_Exception("invalid conversations path : " . print_r($this->path, true));
        }
        return $Controller;
    }
   
    private function follow_conversations_updates_path() {
        $updates = json_decode(try_array($this->path, 2), true);
        //multi update needs to be handled for manager
        //$updates = $updates[0];

        return $this->Factory->build_existing_conversations_controller(array(
            "updates" => $updates
            //"conversation_id" => try_array($updates, 'id'),
            //"last_id" => try_array($updates, 'last_id'),
            //"user" => try_array($updates, 'user')
        ));
    }
}
?>
