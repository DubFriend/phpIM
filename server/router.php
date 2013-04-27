<?php
/*
/user_chat/
    GET: bootstrap
    
/manager_dashboard/
    GET: boostrap

/conversations/
    GET: gets metadata of all currently active conversations (manager only)
    POST: create a new conversation

/conversations/{id}/
    GET: request long poll update on conversation
    POST: create new conversation message
    DELETE: end conversation

/conversations/{id}/messages/{id}
    GET: gets message
    PUT: edit message
    DELETE: delete message

/conversations/archive/ 
*/

class Router_Exception extends Exception {}

class Router {
	private $Factory, $path;
	function __construct(array $fig = array()) {
		$this->Factory = $fig['factory'];
		$this->path = explode("/", substr($fig['path'], 1));
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
}
?>
