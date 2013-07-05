<?php
class Manager_Model extends Bootstrap_Model {
    function initial_data() {
        return array(
            "conversation" => array(
                "join_conversation_button_name" => "Join"
            ),
            "chat" => $this->get_chat_box_data(),
            "js" => array_merge(
                $this->get_base_javascript(),
                $this->get_manager_javascript()
            )
        );
    }

    private function get_manager_javascript() {
        $js = null;
        switch(DEPLOYMENT) {
            case "development":
                $js = array(
                    PUBLIC_ROOT . "js/manager/manager.js",
                    PUBLIC_ROOT . "js/manager/execute.js"
                );
                break;
            case "production":
                $js = array(
                    PUBLIC_ROOT . "phpIM_manager.min.js"
                );
                break;
            default:
                throw new Exception("invalid deployment type");
        }
        return $js;
    }
}

class Manager_View extends Bootstrap_View {
    function render(array $data = array()) {
        return $this->render_full_page($data);
    }

    private function render_full_page(array $data) {
        return  "" .
        "<!DOCTYPE html>" .
        "<html>" .
            $this->Templator->render($this->template_head(), try_array($data, "head", array())) .
            "<body>" .
                "<div class='container'>" .
                    $this->Templator->render(
                        $this->template_conversations(),
                        try_array($data, "conversation")
                    ) .
                    "<button id='get-available-conversations' class='btn'>Get Conversations</button>" .
                    "<input type='text' id='phpIM-username' placeholder='username'/>" .
                    "<button id='phpIM-start-conversation' class='btn'>" .
                        "Start Conversation" .
                    "</button>" .
                "</div>" .
                $this->Templator->render($this->template_js(), array("js" => try_array($data, "js", array()))) .
            "</body>" .
        "</html>";
    }

    private function template_conversations() {
        return "" .
        "<div id='phpIM-conversations'>" .
            /*
            "{{#available}}" .
                "<div class='available-conversation'>" .
                    "<p>ID : {{id}}</p>" .
                    "<p>User : {{user}}</p>" .
                    "<p>Last Update Check : {{last_update_check}}</p>" .
                    "<button class='join-button btn' id='{{id}}'>" .
                        "{{join_conversation_button_name}}" .
                    "</button>" .
                "</div>" .
            "{{/available}}" .
            */

            "<h3>Available Conversations</h3>" .
            "<div id='phpIM-available'></div>" .

            /*
            "<input type='text' id='conversation-id' placeholder='conversation id'/>" .
            "<button id='join-conversation' class='btn'>Join Conversation</button>" .
            */
        "</div>";
    }
}

class Manager_Controller extends Controller {
    function get() {
        return $this->View->render($this->Model->initial_data());
    }
}
?>
