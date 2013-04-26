<?php
require_once "define.php";
require_once "lib.php";
require_once "sql.php";

class Sql_Test extends PHPUnit_Framework_TestCase {

    private $Sql, $DB;

    function setUp() {
        $this->DB = new PDO("sqlite::memory:");
        $this->Sql = new Sequel(array(
            "connection" => $this->DB
        ));
        $this->create_database();
        $this->insert_default_rows();
    }

    private function create_database() {
        $this->DB->exec(
            "CREATE TABLE IF NOT EXISTS A (
                id INT AUTO_INCREMENT PRIMARY KEY,
                a CHAR(3),
                b INT
            )"
        );
    }

    private function insert_default_rows() {
        $this->DB->prepare("INSERT INTO A (id, a, b) VALUES (?, ?, ?)")->execute(array(1, "foo", 5));
        $this->DB->prepare("INSERT INTO A (id, a, b) VALUES (?, ?, ?)")->execute(array(2, "bar", 6));
    }


    function test_results_count() {
        $Results = $this->Sql->select("* FROM A");
        $this->assertEquals(2, $Results->count());
    }

    function test_results_next() {
        $Results = $this->Sql->select("* FROM A");
        $this->assertEquals(array("id" => 1,"a" => "foo", "b" => 5), $Results->next());
    }

    function test_results_foreach_loop() {
        $ResultsObject = $this->Sql->select("* FROM A");
        $actualResults = array();
        foreach($ResultsObject as $key => $val) {
            $actualResults[$key] = $val;
        }

        $this->assertEquals(
            array(
                array("id" => 1, "a" => "foo", "b" => 5),
                array("id" => 2,"a" => "bar", "b" => 6)
            ),
            $actualResults
        );
    }

    function test_results_foreach_loop_empty_results() {
        $ResultsObject = $this->Sql->select("* FROM A WHERE a = ?", array("wrong"));
        $actualResults = array();
        foreach($ResultsObject as $key => $val) {
            $actualResults[$key] = $val;
        }
        $this->assertEquals(array(), $actualResults);
    }

    //doesnt support rewind so foreach loop doesnt start at beggining index
    function test_results_foreach_loop_next_allready_called() {
    	$ResultsObject = $this->Sql->select("* FROM A");
    	$ResultsObject->next();
    	$actualResults = array();
    	foreach($ResultsObject as $key => $val) {
    		$actualResults[$key] = $val;
    	}
    	$this->assertEquals(
    		array("1" => array("id" => 2, "a" => "bar", "b" => 6)),
    		$actualResults
    	);
    }

    function test_insert() {
        $this->Sql->insert("A (id, a, b) VALUES (? ,?, ?)", array(3, "baz", 7));
        $Results = $this->DB->query("SELECT * FROM A WHERE id='3'");
        $Results->setFetchMode(PDO::FETCH_ASSOC);
        $this->assertEquals(
            array("id" => 3, "a" => "baz", "b" => 7),
            $Results->fetch()
        );
    }

    function test_insert_id() {
        $id = $this->Sql->insert("A (id, a, b) VALUES (? ,?, ?)", array(3, "baz", 7));
        $this->assertEquals(3, $id);
    }

    function test_update() {
        $this->Sql->update("A SET a = 'edit' WHERE id='1'");
        $Results = $this->DB->query("SELECT * FROM A WHERE id='1'");
        $Results->setFetchMode(PDO::FETCH_ASSOC);
        $this->assertEquals(
            array("id" => 1, "a" => "edit", "b" => 5),
            $Results->fetch()
        );
    }
    
    function test_delete() {
        $this->Sql->delete("A WHERE id='1'");
        $Results = $this->DB->query("SELECT * FROM A WHERE id='1'");
        $this->assertEquals(
            false,
            $Results->fetch()
        );
    }
}
?>