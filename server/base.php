<?php
abstract class Model {
    protected $Database;
    function __construct(array $fig = array()) {
        $this->Database = $fig['database'];
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
