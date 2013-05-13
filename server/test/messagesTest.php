<?php
require_once "define.php";
require_once "library.php";
require_once "sql.php";
require_once "base.php";
require_once "messages.php";
require_once "test/base.php";

class Messages_Model_Test extends PHPUnit_Framework_TestCase {
    private $Model, $Database;

    function setUp() {
        $this->Database = new PDO("sqlite::memory:");
        $this->Model = new Messages_Model(array(
            "database"=> new Sequel(array("connection" => $this->Database))
        ));
        build_test_database($this->Database);
        insert_default_rows($this->Database);
    }

    private function add_message() {
        return $this->Model->add_message(array(
            "conversation_id" => 'conv_id',
            "user" => 'C',
            "message" => 'test add message'
        ));
    }

    function test_insert_message_insert_id() {
        $id = $this->add_message();
        $this->assertEquals(3, $id);
    }

    function test_insert_message() {
        $this->add_message();

        $row = $this->Database->query(
            "SELECT * FROM Message WHERE message = 'test add message'"
        )->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(
            array(
                "user" => 'C',
                "message" => "test add message",
                "conversation_id" => "conv_id"
            ),
            array(
                "user" => $row['user'],
                "message" => $row['message'],
                "conversation_id" => $row['conversation_id']
            )
        );
    }

    function test_insert_message_updates_conversation() {
        $this->add_message();
        $row = $this->Database->query(
            "SELECT * FROM Conversation WHERE id = 'conv_id'"
        )->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(
            array(
                "last_edit" => date("Y-m-d H:i:s"),
                "last_id" => 3
            ),
            array(
                "last_edit" => $row['last_update'],
                "last_id" => $row['last_id']
            )
        );
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage invalid conversation_id
     */
    function test_insert_message_bad_conversation_id() {
        $this->Model->add_message(array(
            "conversation_id" => 'wrong',
            "user" => 'C',
            "message" => 'test add message'
        ));

        $row = $this->Database->query(
            "SELECT * FROM Message WHERE message = 'test add message'"
        )->fetch();
    }
}

class Messages_Model_Mock {
    public $messageFig, $isAddMessageSuccessfull = true;
    function add_message(array $fig = array()) {
        if($this->isAddMessageSuccessfull) {
            $this->messageFig = $fig;
            return 3;
        }
        else {
            throw new Exception("Messages_Model_Mock add_message set to unsuccessfull");
        }
    }
}

class Messages_Controller_Test extends PHPUnit_Framework_TestCase {
    private $Model,
            $Controller;

    function setUp() {
        $this->Model = new Messages_Model_Mock();
    }

    private function build_controller_override(array $fig = array()) {
        return new Messages_Controller(array(
            "post" => try_array($fig, "post", array(
                "user" => "M",
                "message" => "test message"
            )),
            "conversation_id" => try_array($fig, "conversation_id", "conv_id"),
            "server" => try_array($fig, "server", array(
                "REMOTE_ADDR" => try_array($fig, "REMOTE_ADDR", "mock_remote_addr"),
                "HTTP_USER_AGENT" => try_array($fig, "HTTP_USER_AGENT", "mock_http_user_agent"),
                "REQUEST_METHOD" => try_array($fig, "REQUEST_METHOD", "POST")
            )),
            "model" => $this->Model
        ));
    }

    function test_post() {
        $Controller = $this->build_controller_override();
        $this->assertEquals(
            json_encode(array("is_success" => true, "id" => 3)),
            $Controller->respond()
        );
        $this->assertEquals(
            array(
                "user" => "M",
                "message" => "test message",
                "conversation_id" => "conv_id"
            ),
            $this->Model->messageFig
        );
    }

    function test_post_invalid_conversation_id() {
        $this->Model->isAddMessageSuccessfull = false;
        $Controller = $this->build_controller_override();
        $this->assertEquals(
            json_encode(array("is_success" => false)),
            $Controller->respond()
        );
    }
}
?>
