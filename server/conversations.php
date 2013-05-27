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
            "Conversation (id, manager_id, username, last_update_check) VALUES (?, ?, ?, ?)",
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


    private function where_column_equals_multiple_values_sql($idColumnName, $numberOfIds) {
        $idArray = array();
        for($i = 0; $i < $numberOfIds; $i += 1) {
            $idArray[] = "$idColumnName = ?";
        }
        return "WHERE " . implode(" OR ", $idArray);
    }

    private function conversation_where_sql(array $conversations) {
        return $this->where_column_equals_multiple_values_sql("id", count($conversations));
    }

    function is_up_to_date(array $fig = array()) {
        $Results = $this->Database->select(
            "id, last_id FROM Conversation " . $this->conversation_where_sql($fig),
            array_by_column($fig, 'id')
        );

        if($Results->count() === "0") {
            throw new Exception("Invalid conversation_id");
        }
        
        $lastIdArray = array_by_column($fig, 'last_id');
        $resultsIndex = 0;
        $conversationsToUpdate = array();
        
        foreach($Results as $Update) {
            if($Update['last_id'] > $lastIdArray[$resultsIndex]) {
                $conversationsToUpdate[] = $Update['id'];
            }
            $resultsIndex += 1;
        }
        return $conversationsToUpdate;
    }

    function update_last_update_check(array $fig = array()) {
        $this->Database->update(
            "Conversation SET last_update_check = '" . date("Y-m-d H:i:s") .
            "' " . $this->conversation_where_sql($fig),
            $fig
        );
    }

    function get_updates(array $fig = array()) {
        $sql = "id, message, time_stamp FROM Message WHERE conversation_id = ?";
        $values = array($fig['id']);
        if(isset($fig['last_id'])) {
            $sql .= " AND id > ?";
            $values[] = $fig['last_id'];
        }
        if(isset($fig['user'])) {
            $sql .= " AND user = ?";
            $values[] = $fig['user'];
        }
        return $this->Database->select($sql, $values);
    }

}


class Existing_Conversation_Controller extends Controller {
    const INITIAL_SLEEP_TIME = 1000000, //1000000 == 1 second
          UPDATE_SLEEP_TIME  = 1000000,
          MAX_NUM_UPDATES = 25;

    private $Clock,
            $updates;

    function __construct(array $fig = array()) {
        parent::__construct($fig);
        $this->Clock = try_array($fig, "clock", new Clock());
        $this->updates = try_array($fig, 'updates');
    }

    protected function get() {
        $this->Model->update_last_update_check(array_by_column($this->updates, "id"));
        $this->Clock->sleep(self::INITIAL_SLEEP_TIME);
        $numUpdates = 0;
        $response = array();
        while($numUpdates < self::MAX_NUM_UPDATES) {
            $numUpdates += 1;
            $conversationsToUpdate = $this->Model->is_up_to_date($this->updates);
            if(count($conversationsToUpdate) === 0) {
                $this->Clock->sleep(self::UPDATE_SLEEP_TIME);
            }
            else {
                $updateIndex = 0;
                foreach($conversationsToUpdate as $conv) {
                    $response[$conv] = $this->Model->get_updates(array(
                        "id" => $conv,
                        "last_id" => try_array($this->updates[$updateIndex], 'last_id'),
                        "user" => try_array($this->updates[$updateIndex], 'user')
                    ))->to_array();
                    $updateIndex += 1;    
                }
                break;
            }
        }
        $response = $response !== array() ? $response : "Update Response Timeout";
        return json_encode($response);
    }
}



//NOTE: this class depends on Existing_Conversation_Controller's class constants
class Conversations_Model extends Model {
    //max age in seconds of a conversations last update
    //to still be considered a live conversation
    const MAX_LIVE_AGE_TIME_BUFFER = 10;
    private $maxLiveAge, $Clock;

    function __construct(array $fig = array()) {
        parent::__construct($fig);

        $this->Clock = try_array($fig, 'clock', new Clock());

        $initialSleepTime = Existing_Conversation_Controller::INITIAL_SLEEP_TIME / 1000000;
        $updateSleepTime = Existing_Conversation_Controller::UPDATE_SLEEP_TIME / 1000000;
        $maxNumUpdates = Existing_Conversation_Controller::MAX_NUM_UPDATES;

        $this->maxLiveAge = $initialSleepTime + 
                           ($updateSleepTime * $maxNumUpdates) +
                            self::MAX_LIVE_AGE_TIME_BUFFER;
    }

    function get_live_conversations() {
        $expirationDate = date("Y-m-d H:i:s", $this->Clock->time() - $this->maxLiveAge);
        $Results = $this->Database->select(
            "id, manager_id, username, last_update_check, last_id " .
            "FROM Conversation WHERE last_update_check > ?",
             array($expirationDate)
        );
        return $Results;
    }
}

class Live_Conversations_Controller extends Controller {
    function get() {
        return json_encode($this->Model->get_live_conversations()->to_array());
    }
}
?>
