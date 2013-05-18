<?php
require_once "define.php";
require_once "library.php";
require_once "factory.php"; //need to pull in the Factory_Interface for the mock
require_once "router.php";

class Factory_Mock implements Factory_Interface {
    public $conversationId,
           $lastMessageId,
           $user;
    
    function build_manager_controller() {
        return "manager_controller";
    }

    function build_user_controller() {
        return "user_controller";
    }

    function build_new_conversations_controller() {
        return "new_conversations_controller";
    }

    function build_existing_conversations_controller(array $fig = array()) {
        $this->conversationId = try_array($fig, 'conversation_id');
        $this->lastMessageId = try_array($fig, 'last_id');
        $this->user = try_array($fig, 'user');
        return "existing_conversations_controller";
    }

    function build_messages_controller(array $fig = array()) {
        $this->conversationId = try_array($fig, 'conversation_id');
        return "messages_controller";
    }

    function build_live_conversations_controller() {
        return "live_conversations_controller";
    }
}

class Router_Test extends PHPUnit_Framework_TestCase {
    private $Factory;

    function setUp() {
        $this->Factory = new Factory_Mock();
    }

    private function route($path) {
        $Router = new Router(array(
            "factory" => $this->Factory,
            "path" => "/" . $path
        ));
        return $Router->build_controller();
    }

    function test_connect_path() {
        $this->assertEquals(
            "new_conversations_controller",
            $this->route("conversations")
        );
        $this->assertEquals(
            "new_conversations_controller",
            $this->route("conversations/")
        );
    }

    function test_case_insensitive() {
        $this->assertEquals(
            "new_conversations_controller",
            $this->route("CONVERSATIONS")
        );
    }

    function test_send_message_path() {
        $this->assertEquals(
            "messages_controller",
            $this->route("conversations/messages")
        );
    }

    function test_update_first_update() {
        $this->assertEquals(
            "existing_conversations_controller",
            $this->route("conversations/updates/" . json_encode(array(array("id" => 3))))
        );
        $this->assertEquals(3, $this->Factory->conversationId);
    }
/*
    function test_update_first_update_manager_only() {
        $this->assertEquals(
            "existing_conversations_controller",
            $this->route("conversations/3/manager")
        );
        $this->assertEquals(3, $this->Factory->conversationId);
        $this->assertEquals("M", $this->Factory->user);
    }

    function test_update_first_update_client_only() {
        $this->assertEquals(
            "existing_conversations_controller",
            $this->route("conversations/3/client")
        );
        $this->assertEquals(3, $this->Factory->conversationId);
        $this->assertEquals("C", $this->Factory->user);
    }

    function test_update_since_last_id() {
        $this->assertEquals(
            "existing_conversations_controller",
            $this->route("conversations/3/messages_since/4")
        );
        $this->assertEquals(3, $this->Factory->conversationId);
        $this->assertEquals(4, $this->Factory->lastMessageId);
    }

    function test_update_since_last_id_client_only() {
        $this->assertEquals(
            "existing_conversations_controller",
            $this->route("conversations/3/messages_since/4/client")
        );
        $this->assertEquals(3, $this->Factory->conversationId);
        $this->assertEquals(4, $this->Factory->lastMessageId);
        $this->assertEquals("C", $this->Factory->user);
    }

    function test_update_since_last_id_manager_only() {
        $this->assertEquals(
            "existing_conversations_controller",
            $this->route("conversations/3/messages_since/4/manager")
        );
        $this->assertEquals(3, $this->Factory->conversationId);
        $this->assertEquals(4, $this->Factory->lastMessageId);
        $this->assertEquals("M", $this->Factory->user);
    }
*/
    /**
     * @expectedException Router_Exception
     * @expectedExceptionMessage invalid base level path
     */
    function test_invalid_base_path() {
        $this->route("wrong");
    }

    /**
     * @expectedException Router_Exception
     * @expectedExceptionMessage invalid conversations path
     */
    function test_invalid_conversation_path() {
        $this->route("conversations/3/wrong");
    }

    function test_live_conversations_path() {
        $this->assertEquals(
            "live_conversations_controller",
            $this->route("conversations/live")
        );
    }

}
?>
