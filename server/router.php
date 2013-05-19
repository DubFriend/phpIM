<?php
/*********************************************************************
                         URI Schema

//[a-z] optional group

//update
//GET  conversations/{{id}}/messages_since[a]/{{last_id}}[a]/client[b]

//connect
//POST  conversations

//get live conversations
//GET conversations/live

//send_message
//POST  conversations/{{id}}/messages

********************************************************************/

class Router_Exception extends Exception {}

class Router {
    private $Factory,
            $path,
            $stringPath;

    function __construct(array $fig = array()) {
        $this->Factory = $fig['factory'];
        //remove preceding and trailing slashes and lowercase
        $this->stringPath = strtolower(substr(remove_trailing($fig['path'], '/'), 1));
        $path = explode("/", $this->stringPath);
        foreach($path as $key => $value) {
            $path[$key] = $value;
        }
        $this->path = $path;
    }

    function build_controller() {
        debug(print_r($this->path, true));
        //print_r($this->path);
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
                //treat this level as a conversation_id
                //$Controller = $this->follow_conversations_path_level_2();
                throw new Router_Exception("invalid conversations path : " . print_r($this->path, true));
        }
        return $Controller;
    }

   
    private function follow_conversations_updates_path() {
        $updates = json_decode(try_array($this->path, 2), true);
        //multi update needs to be handled for manager
        $updates = $updates[0];

        return $this->Factory->build_existing_conversations_controller(array(
            "conversation_id" => try_array($updates, 'id'),
            "last_id" => try_array($updates, 'last_id'),
            "user" => try_array($updates, 'user')
        ));
    }
}
?>
