<?php
class User_Model extends Model {
    function initial_data() {
        return array(
            "chat" => array(
                "messages" => array(
                    array(
                        "username" => "Bob",
                        "body" => "message 1",
                        "time" => "time"
                    ),
                    array(
                        "username" => "Alice",
                        "body" => "message 2"
                    )
                ),
                "form" => array(
                    "placeholder" => "Enter message.",
                    "button_name" => "Submit"
                )
            ),
            "js" => array(
                PUBLIC_ROOT . "jquery-1.9.1.min.js",
                PUBLIC_ROOT . "js/base.js",
                PUBLIC_ROOT . "js/user.js"
            )
        );
    }
}

class User_View extends View {
    function render(array $data = array()) {
        return $this->render_full_page($data);
    }

    private function render_full_page(array $data) {
        return  "" .
        "<html>" .
            $this->Templator->render($this->template_head(), try_array($data, "head", array())) .
            "<body>" .
                $this->Templator->render($this->template_chat_box(), try_array($data, "chat", array())) .
                $this->Templator->render($this->template_js(), array("js" => try_array($data, "js", array()))) .
            "</body>" .
        "</html>";
    }

    private function template_head() {
        return "" .
        "<head>" .
        "</head>";
    }

    private function template_chat_box() {
        return "" .
        "<div id='phpIM'>" .
            "<div id='phpIM-message-area'>" .
                "{{#messages}}" .
                    "<div class='message'>" .
                        "<p>{{username}}</p>" .
                        "<p>{{body}}</p>" .
                        "<p>{{time}}</p>" .
                    "</div>" .
                "{{/messages}}" .
            "</div>" .
            "<form id='phpIM-form'>" .
                "<textarea name='message' placeholder='{{form.placeholder}}'></textarea>" .
                "<input type='submit' value='{{form.button_name}}'/>" .
            "</form>" .
        "</div>";
    }
}

class User_Controller extends Controller {
    function get() {
        return $this->View->render($this->Model->initial_data());
    }
}
?>
