<?php
abstract class Model {
    protected $DB;
    function __construct(array $fig = array()) {
        $this->DB = $fig['database'];
    }
}

abstract class View {
    protected $Templator;
    function __construct(array $fig = array()) {
        $this->Templator = $fig['templator'];
    }

    abstract function render(array $data = array());

    protected function template_js() {
        return '{{#js}}<script src="{{.}}"></script>{{/js}}';
    }
}

abstract class Controller {
    protected $get, $post, $server, $Model, $View;
    function __construct(array $fig = array()) {
        $this->get = $fig['get'];
        $this->post = $fig['post'];
        $this->server = $fig['server'];
        $this->Model = $fig['model'];
        $this->View = $fig['view'];
    }

    abstract function execute();
}
?>