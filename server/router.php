<?php

class Router_Exception extends Exception {}

class Router {
    private $Factory,
            $path,
            $stringPath;

    function __construct(array $fig = array()) {
        $this->Factory = $fig['factory'];
        //lowercase and remove begginning and trailing slashes
        $this->stringPath = strtolower(
            substr(remove_trailing($fig['path'], '/'), 1)
        );

        $path = $this->parse_route($this->stringPath);

        $this->path = $path;
    }

    //tokenize on '/' character excepting json sections (checks for {} and [])
    private function parse_route($path) {
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
                throw new Router_Exception(
                    "invalid conversations path : " . print_r($this->path, true)
                );
        }
        return $Controller;
    }

    private function follow_conversations_updates_path() {
        $updates = json_decode(try_array($this->path, 2), true);
        return $this->Factory->build_existing_conversations_controller(array(
            "updates" => $updates
        ));
    }
}
?>
