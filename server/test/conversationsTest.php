<?php
require_once "define.php";
require_once "library.php";
require_once "sql.php";
require_once "base.php";
require_once "conversations.php";

function build_test_database($Database) {
    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Message (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user CHAR(1),
            message VARCHAR(4096),
            conversation_id CHAR(65),
            time_stamp DATETIME
        )"
    );

    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Conversation (
            id CHAR(65) PRIMARY KEY,
            manager_id INT UNSIGNED,
            username,
            last_edit DATETIME,
            last_id INT UNSIGNED
        )"
    );

    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Manager (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(32) UNIQUE,
            password CHAR(128),
            access_level INT UNSIGNED,
            failed_attempts INT
        )"
    );

    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Ip_Check (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip CHAR(45) UNIQUE,
            failed_attempts INT
        )"
    );   
}


function insert_default_rows($Database) {
    $Database->query(
        "INSERT INTO Conversation (id, last_edit, last_id)
         VALUES ('conv_id', '2013-01-01 10:10:10', 2)"
    );

    $Database->query(
        "INSERT INTO Message (id, conversation_id, user, message, time_stamp)
         VALUES (1, 'conv_id', 'M', 'manager message', '2013-01-01 10:10:09')"
    );

    $Database->query(
        "INSERT INTO Message (id, conversation_id, user, message, time_stamp)
         VALUES (2, 'conv_id', 'C', 'client message', '2013-01-01 10:10:10')"
    );
}


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
                "last_edit" => date("Y-m-d H:i:s"),
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

    function test_is_updated_true() {
        $this->assertTrue($this->Model->is_updated(array(
            "conversation_id" => 'conv_id',
            "last_id" => 2
        )));
    }

    function test_is_updated_false() {
        $this->assertFalse($this->Model->is_updated(array(
            "conversation_id" => 'conv_id',
            "last_id" => 1
        )));
    }





    function test_is_updated_last_id_not_sent() {
        $this->assertFalse($this->Model->is_updated(array(
            "conversation_id" => 'conv_id'
        )));
    }

    function test_is_updated_last_id_not_set() {
        $this->Database->query(
            "UPDATE Conversation SET last_id = NULL
             WHERE conversation_id = 'conv_id'"
        );
        $this->assertTrue($this->Model->is_updated(array(
            "conversation_id" => 'conv_id',
            "last_id" => 1
        )));
    }

    function test_is_updated_both_ids_not_set() {
        $this->Database->query(
            "UPDATE Conversation SET last_id = NULL
             WHERE conversation_id = 'conv_id'"
        );
        $this->assertTrue($this->Model->is_updated(array(
            "conversation_id" => 'conv_id'
        )));
    }





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
}






class Existing_Conversation_Model_Mock {
    public $isUpdatedCountdown = 2,
           $numUpdateChecks = 0,
           $isUpdatedFig,
           $getUpdatesFig;

    function is_updated(array $fig = array()) {
        $this->isUpdatedFig = $fig;
        $this->numUpdatedChecks += 1;
        $this->isUpdatedCountdown -= 1;
        return $this->isUpdatedCountdown > 0;
    }

    function get_updates(array $fig = array()) {
        $this->getUpdatesFig = $fig;
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
            "conversation_id" => try_array($fig, "conversation_id", "foo"),
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

    function test_get_max_update_checks() {
        $this->Model->isUpdatedCountdown = Existing_Conversation_Controller::MAX_NUM_UPDATES + 1;
        $response = $this->Controller->respond();
        $this->assertNull($response);
    }

    function test_is_updated_sent_parameters() {
        $response = $this->Controller->respond();
        $this->assertEquals(
            array(
                "conversation_id" => 'foo',
                "last_id" => 1
            ),
            $this->Model->isUpdatedFig
        );
    }

    function test_get_updates_sent_parameters() {
        $response = $this->Controller->respond();
        $this->assertEquals(
            array(
                "conversation_id" => 'foo',
                "user" => 'M',
                "last_id" => 1
            ),
            $this->Model->getUpdatesFig
        );
    }
}
?>
