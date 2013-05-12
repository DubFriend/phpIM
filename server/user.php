<?php
class User_Model extends Model {
    function initial_data() {

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

        return array(
            "chat" => array(
                "messages" => array(
                    /*array(
                        "username" => "Bob",
                        "body" => "message 1",
                        "time" => "time"
                    )*/
                ),
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
            ),
            "js" => array(
                PUBLIC_ROOT . "jquery-1.9.1.min.js",
                PUBLIC_ROOT . "js/define.js",
                PUBLIC_ROOT . "js/lib.js",
                PUBLIC_ROOT . "js/messenger.js",
                PUBLIC_ROOT . "js/execute.js"
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

class User_Controller extends Controller {
    function get() {
        return $this->View->render($this->Model->initial_data());
    }
}
?>
