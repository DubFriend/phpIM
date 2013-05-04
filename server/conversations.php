<?php
class New_Conversation_Model extends Model {
    const SALT_LENGTH = 25; //max salt length is 25 (database no designed to hold longer strings)

    private function generate_signature($input) {
        $salt = random_string(self::SALT_LENGTH);
        //input == username . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']
        $userHash = sha1($salt . $input);
        return $salt . $userHash;
    }

    function start_conversation(array $fig = array()) {
        $conversationId = $this->generate_signature($fig['username'].$fig['signature']);
        $this->Database->insert(
            "Conversation (id, manager_id, username, last_edit) VALUES (?, ?, ?, ?)",
            array(
                $conversationId,
                try_array($fig, "manager_id"),
                try_array($fig, "username"),
                date("Y-m-d H:i:s")
            )
        );
        return $conversationId;
    }
}


class New_Conversation_Controller extends Controller {
    const MIN_SIGNATURE_LENGTH = 7;

    protected function post() {
        $username = try_array($this->post, "username");
        $signature = $username . $this->server['REMOTE_ADDR'] . $this->server['HTTP_USER_AGENT'];
        if(strlen($signature) >= self::MIN_SIGNATURE_LENGTH) {
            return json_encode(array(
                "id" => $this->Model->start_conversation(array(
                    "username" => $username,
                    "signature" => $signature
                ))
            ));
        }
        else {
            throw new Bad_Request_Exception("signature is too short");
        }
    }
}





class Existing_Conversation_Model extends Model {
    function is_updated(array $fig = array()) {
        $Results = $this->Database->select(
            "last_edit FROM Conversation WHERE id = ?",
            array($fig['conversation_id'])
        )->next();
        return strtotime($Results['last_edit']) <= strtotime($fig['last_update']);
    }

    function get_updates(array $fig = array()) {
        if(isset($fig['last_id'])) {
            $sql = "" .
            "id, message, time_stamp FROM Message " .
            "WHERE id > ? AND conversation_id = ? AND user = ?";
            $values = array($fig['last_id'], $fig['conversation_id'], $fig['user']);
        }
        else {
            $sql = "" .
            "id, message, time_stamp FROM Message " .
            "WHERE conversation_id = ? AND user = ?";
            $values = array($fig['conversation_id'], $fig['user']);
        }

        return $this->Database->select($sql, $values);
    }
}


class Existing_Conversation_Controller extends Controller {
    const INITIAL_SLEEP_TIME = 5000000; //5 seconds
    const UPDATE_SLEEP_TIME = 1000000; //1 seconds
    const MAX_NUM_UPDATES = 30;


    protected function post() {
        $numUpdates = 0;
        usleep(self::INITIAL_SLEEP_TIME);
        while(!$this->Model->is_updated() and $numUpdates < self::MAX_NUM_UPDATES) {
            $numUpdates += 1;
            usleep(self::UPDATE_SLEEP_TIME);
        }
        return json_encode($this->Model->get_new_messages());
    }
}
?>
