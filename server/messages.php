<?php
class Messages_Model extends Model {
    function add_message(array $fig = array()) {
        //check that conversation exists
        if($this->Database->select(
            "id FROM Conversation WHERE id = ?",
            array($fig['conversation_id']))->next()
        ) {

            $insertId = $this->Database->insert(
                "Message (conversation_id, user, message) VALUES (?, ?, ?)",
                array($fig['conversation_id'], $fig['user'], $fig['message'])
            );
            $this->Database->update(
                "Conversation SET last_update_check = ?, last_id = ? WHERE id = ?",
                array(date("Y-m-d H:i:s"), $insertId, $fig['conversation_id'])
            );
            return $insertId;
        }
        else {
            throw new Exception("invalid conversation_id");
        }
    }
}


class Messages_Controller extends Controller {
    function post() {
        try {
            $insertId = $this->Model->add_message(array(
                "user" => try_array($this->post['messages'][0], 'user'),
                "message" => try_array($this->post['messages'][0], 'message'),
                "conversation_id" => try_array($this->post['messages'][0], 'conversation_id')
            ));
            return json_encode(array(
                "is_success" => true,
                "id" => $insertId
            ));
        }
        catch (Exception $e) {
            return json_encode(array("is_success" => false));
        }
    }
}
?>
