<?php
/*********************************************************************
                         URI Schema

/user_chat
    GET: bootstrap
    
/manager_dashboard
    GET: boostrap

/conversations
    GET: gets metadata of all currently active conversations
    POST: create a new conversation

/conversations/{id}
    GET: request long poll update on conversation
    POST: create new conversation message
    DELETE: end conversation

/conversations/{id}/messages
    GET: returns all messages from conversation
    DELETE: deletes conversation. 

/conversations/{id}/messages/{id}
    GET: gets message
    PUT: edit message
    DELETE: delete message

/conversations/archive
    GET: metadata for last N conversations (pagination)

/conversations/archive/{id}
    GET: gets all data for a specific conversation
    PUT: edit conversation's metadata
    DELETE: permananently deletes conversation

/conversations/archive/{id}/messages

/conversations/archive/{id}/messages/{id}

/user
    GET: list of current managers
    PUT: edit profile metadata
    POST: create a manager profile

/user/{id}
    GET: details of selected manager
    PUT: edit manager profile
    DELETE: delete manager profile

/settings
    GET: view app settings
    PUT: edit app settings
    POST: create a new setting
    DELETE: reset settings to default

/settings/user

/settings/user/{id}

***************************************************************/

class Router_Exception extends Exception {}

class Router {
    private $Factory, $path;
    function __construct(array $fig = array()) {
        $this->Factory = $fig['factory'];
        $path = explode("/", substr($fig['path'], 1));
        foreach($path as $key => $value) {
            $path[$key] = strtolower($value);
        }
        $this->path = $path;
    }

    function build_controller() {
        debug(print_r($this->path, true));

        $Controller = null;
        switch($this->path[0]) {
            case "user_chat":
                $Controller = $this->Factory->build_user_controller();
                break;
            case "manager_dashboard":
                $Controller = $this->Factory->build_manager_controller();
                break;
            case "conversations":
                $Controller = $this->follow_conversations_path();
                break;
            default:
                throw new Router_Exception("Base Level Router Error");	
        }

        return $Controller;
    }

    private function follow_conversations_path() {
        $Controller = null;
        //check that messages data isnt selected
        if(! try_array($this->path, 2)) {
            //check that conversation id given (existing conversation)
            if(try_array($this->path, 1)) {
                $Controller = $this->Factory->build_existing_conversations_controller(array(
                    "conversationsId" => $this->path[1]
                ));
            }
            else {
                $Controller = $this->Factory->build_new_conversations_controller();
            }
        }
        else if($this->path[2] == "messages") {
            $Controller = $this->Factory->build_new_messages_controller(array(
                "conversationId" => try_array($this->path, 1),
                "messagesId" => try_array($this->path, 3)
            ));
        }

        return $Controller;
    }
}
?>
