<?php
class New_Conversation_Model extends Model {
    const SALT_LENGTH = 20;

    private function generate_signature($input) {
        $salt = random_string(self::SALT_LENGTH);
        //input == username . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']
        $userHash = sha1($salt . $input);
        return $salt . $userHash;
    }

    function start_conversation(array $fig = array()) {
        $conversationId = $this->generate_signature($fig['username'].$fig['signature']);
        return $conversationId;
    }
}


class New_Conversation_Controller extends Controller {
    const INITIAL_SLEEP_TIME = 5000000; //5 seconds
    const UPDATE_SLEEP_TIME = 1000000; //1 seconds
    const MAX_UPDATE_CHECKS = 30;


    protected function post() {
        usleep(self::INITIAL_SLEEP_TIME);
        while(!$this->is_updated()) {
            usleep(self::UPDATE_SLEEP_TIME);
        }
        return "conversations response";
    }

    private function is_updated() {
        return rand(0, 1);
    }
}


class Existing_Conversation_Model extends Model {

}


class Existing_Conversation_Controller extends Controller {

}
?>
