<?php
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
				a CHAR(3),
				b INT
			)"
		);
	}

	private function insert_default_rows() {
		$this->DB->prepare("INSERT INTO A (a, b) VALUES (?, ?)")
			 ->execute(array("foo", 5));
	}

	function test_count() {
		$Results = $this->Sql->select("* FROM A");
		$this->assertEquals(1, $Results->count());
		//$this->assertEquals(array("a" => "foo", "b" => 5), $Results->next());
	}

	

	//function test_results_work_with_foreach_loop() {}
	//function test_insert() {}
	//function test_update() {}
	//function test_delete() {}
}
?>