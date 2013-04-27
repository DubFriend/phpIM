<?php
class Factory {
    private $get,
            $post,
            $server,
            $session,
            $Database;

    function __construct(array $fig = array()) {
        $this->get = $fig['get'];
        $this->post = $fig['post'];
        $this->server = $fig['server'];
        $this->session = $fig['session'];
        $this->Database = $fig['database'];
    }

    private function build_templator() {
        return new Mustache_Engine;
    }

    private function build_manager_model() {
        return new Manager_Model(array(
            "database" => $this->Database
        ));
    }

    private function build_manager_view() {
        return new Manager_View(array(
            "templator" => $this->build_templator()
        ));
    }

    function build_manager_controller() {
        return new Manager_Controller(array(
            "get" => $this->get,
            "post" => $this->post,
            "server" => $this->server,
            "model" => $this->build_manager_model(),
            "view" => $this->build_manager_view()
        ));
    }

    private function build_user_model() {
        return new User_Model(array(
            "database" => $this->Database
        ));
    }

    private function build_user_view() {
        return new User_View(array(
            "templator" => $this->build_templator()
        ));
    }

    function build_user_controller() {
        return new User_Controller(array(
            "get" => $this->get,
            "post" => $this->post,
            "server" => $this->server,
            "model" => $this->build_user_model(),
            "view" => $this->build_user_view()
        ));
    }
}
?>
