<?php
require_once "define.php";
require_once "library.php";
require_once "sql.php";
require_once "base.php";
require_once "conversations.php";
require_once "test/base.php";

class New_Conversation_Model_Test extends PHPUnit_Framework_TestCase {
    private $Database, $Model;

    function setUp() {
        $this->Database = new PDO("sqlite::memory:");
        $this->Model = new New_Conversation_Model(array(
            "database"=> new Sequel(array("connection" => $this->Database))
        ));
        build_test_database($this->Database);
    }

    
    private function generate_signature($input) {
        $salt = random_string(New_Conversation_Model::SALT_LENGTH);
        $userHash = sha1($salt . $input);//$this->server['REMOTE_ADDR'] . $this->server['HTTP_USER_AGENT']);
        return $salt . $userHash;
    }

    private function is_signature_match($submittedSignature, $storedSignature) {
        $salt = substr($storedSignature, 0, New_Conversation_Model::SALT_LENGTH);
        $originalHash = substr($storedSignature, New_Conversation_Model::SALT_LENGTH, 40);
        $submittedHash = sha1($salt . $submittedSignature);
        return $submittedHash === $originalHash ? true : false;
    }

    private function start_conversation() {
        return $this->Model->start_conversation(array(
            "username" => "username",
            "signature" => "REMOTE_ADDRHTTP_USER_AGENT"
        ));
    }

    function test_start_conversation_correct_id() {
        $conversationId = $this->start_conversation();
        $this->assertTrue($this->is_signature_match(
            "usernameREMOTE_ADDRHTTP_USER_AGENT",
            $conversationId
        ));
    }

    function test_start_conversation_signature_without_salt_is_40_characters() {
        $this->assertEquals(
            40,
            strlen($this->start_conversation()) - New_Conversation_Model::SALT_LENGTH
        );
    }

    function test_start_conversation_results_logged() {
        $conversationId = $this->start_conversation();

        $Results = $this->Database->query(
            "SELECT * FROM Conversation WHERE id = '$conversationId'"
        );
        $row = $Results->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(
            array(
                "id" => $conversationId,
                "manager_id" => null,
                "username" => "username",
                "last_update_check" => date("Y-m-d H:i:s"),
                "last_id" => null
            ),
            $row
        );
    }
}



class New_Conversation_Model_Mock {
    function start_conversation(array $fig = array()) {
        return "mock_conversation_id";
    }
}

class New_Conversation_Controller_Test extends PHPUnit_Framework_TestCase {
    private $Controller;

    function setUp() {
        $this->Controller = $this->build_controller_override();
    }

    private function build_controller_override(array $fig = array()) {
        return new New_Conversation_Controller(array(
            "post" => try_array($fig, "post", array("username" => "mock_username")),
            "server" => try_array($fig, "server", array(
                "REMOTE_ADDR" => try_array($fig, "REMOTE_ADDR", "mock_remote_addr"),
                "HTTP_USER_AGENT" => try_array($fig, "HTTP_USER_AGENT", "mock_http_user_agent"),
                "REQUEST_METHOD" => try_array($fig, "REQUEST_METHOD", "POST")
            )),
            "model" => new New_Conversation_Model_Mock()
        ));
    }

    function test_post_success() {
        $response = $this->Controller->respond();
        $this->assertEquals(
            json_encode(array("id" => "mock_conversation_id")),
            $response
        );
    }

    function test_post_no_post_data() {
        $Controller = $this->build_controller_override(array(
            "post" => array()
        ));
        $response = $Controller->respond();
        $this->assertEquals(
            json_encode(array("id" => "mock_conversation_id")),
            $response
        );
    }

    /**
     * @expectedException Bad_Request_Exception
     */
    function test_post_signature_too_short() {
        $padLength = New_Conversation_Controller::MIN_SIGNATURE_LENGTH / 2;
        $Controller = $this->build_controller_override(array(
            "post" => array("username" => null),
            "server" => array(
                "REMOTE_ADDR" => str_pad("", $padLength - 1, "x"),
                "HTTP_USER_AGENT" => str_pad("", $padLength, "y"),
                "REQUEST_METHOD" => "POST"
            )
        ));
        $Controller->respond();
    }
}




class Existing_Conversations_Model_Test extends PHPUnit_Framework_TestCase {
    private $Model, $Database;
    function setUp() {
        $this->Database = new PDO("sqlite::memory:");
        $this->Model = new Existing_Conversation_Model(array(
            "database"=> new Sequel(array("connection" => $this->Database))
        ));
        build_test_database($this->Database);
        insert_default_rows($this->Database);
    }

    function test_last_update_check_is_updated() {
        $this->Model->update_last_update_check(array(
            "conversation_id" => 'conv_id'
        ));

        $Results = $this->Database->query(
            "SELECT last_update_check FROM Conversation WHERE id = 'conv_id'"
        );
        $row = $Results->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(
            date("Y-m-d H:i:s"),
            $row['last_update_check']
        );
    }

    function test_is_up_to_date_true() {
        $this->assertTrue($this->Model->is_up_to_date(array(
            array("conversation_id" => 'conv_id',
            "last_id" => 2)
        )));
    }

    function test_is_up_to_date_false() {
        $this->assertFalse($this->Model->is_up_to_date(array(
            array("conversation_id" => 'conv_id',
            "last_id" => 1)
        )));
    }

    function test_is_up_to_date_last_id_not_sent() {
        $this->assertFalse($this->Model->is_up_to_date(array(
            array("conversation_id" => 'conv_id')
        )));
    }

    function test_is_up_to_date_last_id_not_set() {
        $this->Database->query(
            "UPDATE Conversation SET last_id = NULL
             WHERE id = 'conv_id'"
        );
        $this->assertTrue($this->Model->is_up_to_date(array(
            array("conversation_id" => 'conv_id')
        )));
        $this->assertTrue($this->Model->is_up_to_date(array(
            array("conversation_id" => 'conv_id',
            "last_id" => 1) //this wouldnt be set in this case, but here as a corner-case
        )));
    }

    function test_is_up_to_date_both_ids_not_set() {
        $this->Database->query(
            "UPDATE Conversation SET last_id = NULL
             WHERE id = 'conv_id'"
        );
        $this->assertTrue($this->Model->is_up_to_date(array(
            array("conversation_id" => 'conv_id')
        )));
    }

    /**
     * @expectedException Exception
     */
    function test_is_up_to_date_invalid_conversation_id() {
        $this->Model->is_up_to_date(array("conversationId" => 'wrong'));
    }


/*
    function test_is_up_to_date_on_multiple_conversations() {
        $this->Database->query(
            "INSERT INTO Conversation (id, last_update_check, last_id)
             VALUES ('conv_id_2', '2013-01-01 10:10:10', 3)"
        );
        $this->assertTrue($this->Model->is_up_to_date(array(

        )));

    }
*/


    function test_get_updates() {
        $updates = $this->Model->get_updates(array(
            "conversation_id" => 'conv_id',
            "user" => 'C',
            "last_id" => 1
        ));

        $this->assertEquals(
            array(
                "id" => 2,
                "message" => 'client message',
                "time_stamp" => '2013-01-01 10:10:10'
            ),
            $updates->next()
        );

        $this->assertFalse($updates->next());
    }
    
    function test_get_updates_no_updates() {
        $updates = $this->Model->get_updates(array(
            "conversation_id" => 'conv_id',
            "user" => 'C',
            "last_id" => 2
        ));
        $this->assertFalse($updates->next());
    }

    function test_get_updates_own_updates() {
        $updates = $this->Model->get_updates(array(
            "conversation_id" => 'conv_id',
            "user" => 'M',
            "last_id" => 1
        ));
        $this->assertFalse($updates->next());
    }

    function test_get_updates_all_manager_messages() {
        $updates = $this->Model->get_updates(array(
            "conversation_id" => 'conv_id',
            "user" => 'M'
        ));

        $this->assertEquals(
            array(
                "id" => 1,
                "message" => 'manager message',
                "time_stamp" => '2013-01-01 10:10:09'
            ),
            $updates->next()
        );

        $this->assertFalse($updates->next());
    }

    function test_get_updates_for_any_user() {
        $updates = $this->Model->get_updates(array(
            "conversation_id" => 'conv_id',
        ));

        $this->assertEquals(
            array(
                array(
                    "id" => 1,
                    "message" => 'manager message',
                    "time_stamp" => '2013-01-01 10:10:09'
                ),
                array(
                    "id" => 2,
                    "message" => 'client message',
                    "time_stamp" => '2013-01-01 10:10:10'
                )
            ),
            $updates->to_array()
        );
    }
}






class Existing_Conversation_Model_Mock {
    public $isUpdatedCountdown = 2,
           $numUpdateChecks = 0,
           $isUpdatedFig,
           $updateLastUpdateFig,
           $getUpdatesFig;

    function is_up_to_date(array $fig = array()) {
        $this->isUpdatedFig = $fig;
        $this->numUpdatedChecks += 1;
        $this->isUpdatedCountdown -= 1;
        return $this->isUpdatedCountdown > 0;
    }

    function update_last_update_check(array $fig = array()) {
        $this->updateLastUpdateFig = $fig;
    }

    function get_updates(array $fig = array()) {
        $this->getUpdatesFig = $fig;
        return new Mock_Updates_Result();
    }
}

class Mock_Updates_Result {
    function to_array() {
        return "mock update";
    }
}

class Clock_Mock {
    function sleep() {}
}


class Existing_Conversation_Controller_Test extends PHPUnit_Framework_TestCase {
    private $Controller, $Model;

    function setUp() {
        $this->Model = new Existing_Conversation_Model_Mock();
        $this->Controller = $this->build_controller_override();
    }

    function build_controller_override(array $fig = array()) {
        return new Existing_Conversation_Controller(array(
            "clock" => new Clock_Mock(),
            "last_id" => try_array($fig, "last_id", 1),
            "user" => "M",
            "conversation_id" => try_array($fig, "conversation_id", "conv_id"),
            "server" => try_array($fig, "server", array(
                "REQUEST_METHOD" => try_array($fig, "REQUEST_METHOD", "GET")
            )),
            "model" => $this->Model
        ));
    }

    function test_get() {
        $response = $this->Controller->respond();
        $this->assertEquals(json_encode("mock update"), $response);
    }

    function test_get_updates_last_update_check() {
        $response = $this->Controller->respond();
        $this->assertEquals(
            array('conversation_id' => 'conv_id'),
            $this->Model->updateLastUpdateFig
        );
    }

    function test_get_max_update_checks() {
        $this->Model->isUpdatedCountdown = Existing_Conversation_Controller::MAX_NUM_UPDATES + 1;
        $response = $this->Controller->respond();
        $this->assertEquals(json_encode("Update Response Timeout"), $response);
    }

    function test_is_updated_sent_parameters() {
        $response = $this->Controller->respond();
        $this->assertEquals(
            array(array(
                "conversation_id" => 'conv_id',
                "last_id" => 1
            )),
            $this->Model->isUpdatedFig
        );
    }

    function test_get_updates_sent_parameters() {
        $response = $this->Controller->respond();
        $this->assertEquals(
            array(
                "conversation_id" => 'conv_id',
                "user" => 'M',
                "last_id" => 1
            ),
            $this->Model->getUpdatesFig
        );
    }
}

class Clock_Expire_Mock {
    private $expirationTime;
    function __construct($expirationDiff = 0) {
        $initialSleepTime = Existing_Conversation_Controller::INITIAL_SLEEP_TIME / 1000000;
        $updateSleepTime = Existing_Conversation_Controller::UPDATE_SLEEP_TIME / 1000000;
        $maxNumUpdates = Existing_Conversation_Controller::MAX_NUM_UPDATES;
        $timeBuffer = Conversations_Model::MAX_LIVE_AGE_TIME_BUFFER;
        $this->expirationTime = $initialSleepTime +
                               ($updateSleepTime * $maxNumUpdates) +
                                $timeBuffer + $expirationDiff;
    }

    function time() {
        return strtotime("2013-01-01 10:10:09") + $this->expirationTime;
    }
}


class Conversations_Model_Test extends PHPUnit_Framework_TestCase {
    private $Model, $Database;
    function setUp() {
        $this->Database = new PDO("sqlite::memory:");
        build_test_database($this->Database);
        insert_default_rows($this->Database);
    }

    private function build_model_override($expirationTimeDifferential = 0) {
        return new Conversations_Model(array(
            "database" => new Sequel(array("connection" => $this->Database)),
            "clock" => new Clock_Expire_Mock($expirationTimeDifferential)
        ));
    }

    function test_get_live_conversations_within_expiration() {
        $Model = $this->build_model_override();
        $Results = $Model->get_live_conversations();
        $this->assertEquals(1, $Results->count());

        $this->assertEquals(
            array(
                "id" => 'conv_id',
                "last_update_check" => '2013-01-01 10:10:10',
                "last_id" => 2,
                "manager_id" => null,
                "username" => null
            ),
            $Results->next()
        );
    }

    function test_get_live_conversations_expired() {
        $Model = $this->build_model_override(1);
        $Results = $Model->get_live_conversations();
        $this->assertFalse($Results->next());
    }
}
?>
