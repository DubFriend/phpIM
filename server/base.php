<?php
abstract class Model {
    protected $Database;
    function __construct(array $fig = array()) {
        $this->Database = $fig['database'];
    }
}

//models intended for initial load controllers.
abstract class Bootstrap_Model extends Model {

    //returns the data used for an initial page load.
    abstract function initial_data();

    protected function get_chat_box_data() {
        return array(
            "sendForm" => array(
                "placeholder" => "Enter message.",
                "buttonName" => "Submit"
            ),
            "connectForm" => array(
                "placeholder" => "Username",
                "buttonName" => "Connect"
            ),
            "disconnectForm" => array(
                "buttonName" => "Disconnect"
            )
        );
    }

    protected function get_base_javascript() {
        $js = null;
        switch(DEPLOYMENT) {
            case "development":
                $js = array(
                    PUBLIC_ROOT . "jquery-1.9.1.min.js",
                    PUBLIC_ROOT . "js/define.js",
                    PUBLIC_ROOT . "js/lib.js",
                    PUBLIC_ROOT . "js/messenger.js",
                    PUBLIC_ROOT . "js/execute.js"
                );
                break;
            case "production":
                $js = array(
                    PUBLIC_ROOT . "jquery-1.9.1.min.js",
                    PUBLIC_ROOT . "phpIM.min.js"
                );
            default:
                throw new Exception("invalid deployment type");
        }
        return $js;
    }

}

interface Renderable {
    function render(array $data = array());
}

abstract class View implements Renderable {
    protected $Templator;
    function __construct(array $fig = array()) {
        $this->Templator = $fig['templator'];
    }

    protected function template_js() {
        return '{{#js}}<script src="{{.}}"></script>{{/js}}';
    }
}

abstract class Bootstrap_View extends View {
    protected function template_chat_box() {
        return "" .
        "<div id='phpIM-chat-box'>" .
            "<div id='phpIM-message-area'>" .
                "{{#messages}}" .
                    "<div class='message'>" .
                        "<p>{{username}}</p>" .
                        "<p>{{body}}</p>" .
                        "<p>{{time}}</p>" .
                    "</div>" .
                "{{/messages}}" .
            "</div>" .
            
            "<form id='phpIM-connect'>" .
                "<input type='text' name='username' placeholder='{{connectForm.placeholder}}'/>" .
                "<input type='submit' value='{{connectForm.buttonName}}'/>" .
            "</form>" .
            
            "<button id='phpIM-disconnect'>{{disconnectForm.buttonName}}</button>" .

            "<form id='phpIM-send-message'>" .
                "<textarea name='message' placeholder='{{sendForm.placeholder}}'></textarea>" .
                "<input type='submit' value='{{sendForm.buttonName}}'/>" .
            "</form>" .
        "</div>";
    }
}

class Bad_Request_Exception extends Exception {}

abstract class Controller {
    protected $get, $post, $server, $Model, $View;
    function __construct(array $fig = array()) {
        $this->server = $fig['server'];
        $this->get = try_array($fig, 'get');
        $this->post = try_array($fig, 'post');
        $this->Model = try_array($fig, 'model');
        $this->View = try_array($fig, 'view');
    }
    
    private function default_unimplemented_response($type) {
        throw new Exception("implement me");
    }

    protected function get() { $this->default_unimplemented_response("get"); }
    protected function put() { $this->default_unimplemented_response("put"); }
    protected function post() { $this->default_unimplemented_response("post"); }
    protected function delete() { $this->default_unimplemented_response("delete"); }
    protected function head() { $this->default_unimplemented_response("head"); }
    protected function options() { $this->default_unimplemented_response("options"); }

    protected function error() {
        throw new Exception("implement me");
    }

    function respond() {
        debug(print_r($this->server, true));
        debug(print_r($this->server['REQUEST_METHOD'], true));

        $response = null;
        $method = $this->server['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $response = $this->get();  
                break;
            case 'PUT':
                $response = $this->put();  
                break;
            case 'POST':
                $response = $this->post();  
                break;
            case 'DELETE':
                $response = $this->delete();  
                break;
            case 'HEAD':
                $response = $this->head();  
                break;
            case 'OPTIONS':
                $response = $this->options();    
                break;
            default:
                $response = $this->error();  
                break;
        }

        return $response;
    }
}
?>
