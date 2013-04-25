<?php

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

	function test_simple_select() {
		$Results = $this->Sql->query("SELECT * FROM A");
		$this->assertEquals(1, $Results->count());
		$this->assertEquals(array("a" => "foo", "b" => 5), $Results->next());
	}

	//function test_results_work_with_foreach_loop() {}
	//function test_insert() {}
	//function test_update() {}
	//function test_delete() {}
}
/*
class MyIterator implements Iterator
{
    private $var = array();

    public function __construct($array)
    {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function rewind()
    {
        echo "rewinding\n";
        reset($this->var);
    }
  
    public function current()
    {
        $var = current($this->var);
        echo "current: $var\n";
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->var);
        echo "key: $var\n";
        return $var;
    }
  
    public function next() 
    {
        $var = next($this->var);
        echo "next: $var\n";
        return $var;
    }
  
    public function valid()
    {
        $key = key($this->var);
        $var = ($key !== NULL && $key !== FALSE);
        echo "valid: $var\n";
        return $var;
    }

}
*/
?>