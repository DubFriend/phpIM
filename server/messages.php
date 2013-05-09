<?php
class Messages_Model extends Model {
    function add_message(array $fig = array()) {
        return $this->Database->insert(
            "Message (conversation_id, user, message) VALUES (?, ?, ?)",
            array($fig['conversation_id'], $fig['user'], $fig['message'])
        );
    }
}
?>
