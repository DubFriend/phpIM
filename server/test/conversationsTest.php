<?php
require_once "define.php";
require_once "library.php";
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
            last_edit DATETIME
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

class New_Conversations_Model_Test extends PHPUnit_Framework_TestCase {
    private $Database, $Model;

    function setUp() {
        $this->Database = new PDO("sqlite::memory:");
        $this->Model = new New_Conversation_Model(array(
            "database"=> $this->Database
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

    function test_start_conversation_results_logged() {
        $conversationId = $this->start_conversation();

        $Results = $this->Database->query("SELECT * FROM Conversation WHERE id = '$conversationId'");
        $row = $Results->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(
            array(
                "id" => $conversationId,
                "manager_id" => NULL,
                "last_edit" => date("Y-m-d H:i:s")
            ),
            $row
        );
    }
}
?>
