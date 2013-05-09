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
}
?>