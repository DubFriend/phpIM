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

		$this->DB->prepare("INSERT INTO A (a, b) VALUES (?, ?)")
			 ->execute(array("bar", 6));
	}

	function test_results_count() {
		$Results = $this->Sql->select("* FROM A");
		$this->assertEquals(2, $Results->count());
	}

	function test_results_next() {
		$Results = $this->Sql->select("* FROM A");
		$this->assertEquals(array("a" => "foo", "b" => 5), $Results->next());
	}

	function test_results_work_with_foreach_loop() {
		$ResultsObject = $this->Sql->select("* FROM A");
		$actualResults = array();
		foreach($ResultsObject as $key => $val) {
			$actualResults[$key] = $val;
		}

		$this->assertEquals(
			array(
				array("a" => "foo", "b" => 5),
				array("a" => "bar", "b" => 6)
			),
			$actualResults
		);
	}

	function test_results_work_with_foreach_loop_empty_results() {
		$ResultsObject = $this->Sql->select("* FROM A WHERE a = ?", array("wrong"));
		$actualResults = array();
		foreach($ResultsObject as $key => $val) {
			$actualResults[$key] = $val;
		}
		
		$this->assertEquals(
			array(),
			$actualResults
		);
	}
	//function test_insert() {}
	//function test_update() {}
	//function test_delete() {}
}
?>