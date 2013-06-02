<?php
class User_Model extends Bootstrap_Model {
    function initial_data() {
        return array(
            "chat" => $this->get_chat_box_data(),
            "js" => array_merge(
                $this->get_base_javascript(),
                $this->get_user_javascript()
            )
        );
    }

    private function get_user_javascript() {
        $js = null;
        switch(DEPLOYMENT) {
            case "development":
                $js = array(
                    PUBLIC_ROOT . "js/messenger.js",
                    PUBLIC_ROOT . "js/execute.js"
                );
                break;
            case "production":
                $js = array(
                    PUBLIC_ROOT . "phpIM.min.js"
                );
                break;
            default:
                throw new Exception("invalid deployment type");
        }
        return $js;
    }
}

class User_View extends Bootstrap_View {
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
                    $this->Templator->render($this->template_chat_box(), try_array($data, "chat", array())) .
                "</div>" .
                $this->Templator->render($this->template_js(), array("js" => try_array($data, "js", array()))) .
            "</body>" .
        "</html>";
    }

}

class User_Controller extends Controller {
    function get() {
        return $this->View->render($this->Model->initial_data());
    }
}
?>
