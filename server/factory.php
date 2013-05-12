<?php

interface Factory_Interface {
    function build_manager_controller();
    function build_user_controller();
    function build_new_conversations_controller();
    function build_existing_conversations_controller(array $fig = array());
    function build_messages_controller(array $fig = array());
}

class Factory implements Factory_Interface {
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

    private function default_controller_fig(array $extra = array()) {
        return array_merge($extra, array(
            "get" => $this->get,
            "post" => $this->post,
            "server" => $this->server
        ));
    }

    private function default_view_fig(array $extra = array()) {
        return array_merge($extra, array(
            "templator" => $this->build_templator()
        ));
    }

    private function default_model_fig(array $extra = array()) {
        return array_merge($extra, array(
            "database" => $this->Database
        ));
    }

    private function build_templator() {
        return new Mustache_Engine;
    }

    private function build_manager_model() {
        return new Manager_Model($this->default_model_fig());
    }

    private function build_manager_view() {
        return new Manager_View($this->default_view_fig());
    }

    function build_manager_controller() {
        return new Manager_Controller($this->default_controller_fig(array(
            "model" => $this->build_manager_model(),
            "view" => $this->build_manager_view()
        )));
    }

    private function build_user_model() {
        return new User_Model($this->default_model_fig());
    }

    private function build_user_view() {
        return new User_View($this->default_view_fig());
    }

    function build_user_controller() {
        return new User_Controller($this->default_controller_fig(array(
            "model" => $this->build_user_model(),
            "view" => $this->build_user_view()
        )));
    }

    private function build_new_conversations_model() {
        return new New_Conversation_Model($this->default_model_fig());
    }

    function build_new_conversations_controller() {
        return new New_Conversation_Controller($this->default_controller_fig(array(
            "model" => $this->build_new_conversations_model()
        )));
    }

    function build_messages_controller(array $fig = array()) {
        return new Messages_Controller(array(
            "model" => new Messages_Model(array("database" => $this->Database)),
            "post" => $this->post,
            "server" => $this->server,
            "conversation_id" => try_array($fig, 'conversation_id')
        ));
    }

    function build_existing_conversations_controller(array $fig = array()) {
        return new Existing_Conversation_Controller(array(
            "last_id" => try_array($fig, "last_id"),
            "user" => try_array($this->post, "user"),
            "conversation_id" => try_array($fig, "conversation_id"),
            "server" => $this->server,
            "model" => new Existing_Conversation_Model($this->default_model_fig())
        ));
    }
}
?>
