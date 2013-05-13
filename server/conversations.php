<?php
class New_Conversation_Model extends Model {
    const SALT_LENGTH = 25; //max salt length is 25 (database not designed to hold longer strings)

    private function generate_signature($input) {
        $salt = random_string(self::SALT_LENGTH);
        //input == username . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']
        $userHash = sha1($salt . $input);
        return $salt . $userHash;
    }

    function start_conversation(array $fig = array()) {
        $conversationId = $this->generate_signature($fig['username'].$fig['signature']);
        $this->Database->insert(
            "Conversation (id, manager_id, username, last_update) VALUES (?, ?, ?, ?)",
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

    function is_up_to_date(array $fig = array()) {
        $Results = $this->Database->select(
            "last_id FROM Conversation WHERE id = ?",
            array($fig['conversation_id'])
        )->next();
        
        if(!$Results) {
            throw new Exception("Invalid conversation_id");
        }
        else if($Results['last_id'] === null) {
            return true;
        }
        else if(isset($fig['last_id'])) {
            return ($Results['last_id'] <= $fig['last_id']);
        }
        else {
            return false;
        }
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
    const INITIAL_SLEEP_TIME = 1000000, //1000000 == 1 second
          UPDATE_SLEEP_TIME  = 1000000,
          MAX_NUM_UPDATES = 30;

    private $Clock,
            $conversationId,
            $lastMessageId,
            $userType;

    function __construct(array $fig = array()) {
        parent::__construct($fig);
        $this->Clock = try_array($fig, "clock", new Clock());
        $this->conversationId = try_array($fig, "conversation_id");
        $this->lastMessageId = try_array($fig, "last_id");
        $this->userType = try_array($fig, "user");
    }

    protected function get() {
        $this->Clock->sleep(self::INITIAL_SLEEP_TIME);
        $updateConfig = array(
            "conversation_id" => $this->conversationId,
            "last_id" => $this->lastMessageId
        );
        $getUpdateConfig = array_merge(
            $updateConfig,
            array("user" => $this->userType)
        );
        $numUpdates = 0;
        while($numUpdates < self::MAX_NUM_UPDATES) {
            $numUpdates += 1;
            if($this->Model->is_up_to_date($updateConfig)) {
                $this->Clock->sleep(self::UPDATE_SLEEP_TIME);
            }
            else {
                return json_encode($this->Model->get_updates($getUpdateConfig));
            }
        }
        return json_encode("Update Response Timeout");
    }
}
?>
