<?php
//Wrapper to abstract the method of database access
//(currently implented with PDO)
class Sequel {
    
    private $DB;
    
    function __construct(array $fig = array()) {
        $this->DB = $fig['connection'];
    }

    
    private function extract_select_predicate($query) {
        return substr($query, strpos($query, "FROM"));
    }

    function select($query, array $values = array()) {
        $statement = "SELECT $query";        
        $Results = $this->DB->prepare($statement)->execute($values);
        return new Sequel_Results(array(
            "results" => $Results,
            "predicate" => $this->extract_select_predicate($query),
            "values" => $values,
            "connection" => $this->DB
        ));
    }

    //function insert($query, array $values = null) {}
    //function update($query, array $values = null) {}
    //function delete($query, array $values = null) {}
}

//Results Set Wrapper returned by calls to select
class Sequel_Results implements Iterator {
    private $Results,
            $DB,
            $predicate,
            $values,
            $count;

    function __construct(array $fig = array()) {
        $this->Results = $fig['results'];
        $this->predicate = $fig['predicate'];
        $this->values = $fig['values'];
        $this->DB = $fig['connection'];
    }

    //rowCount doesnt work for sqlite :(
    function count() {
        if($this->count === NULL) {
            $sql= "SELECT count(*) " . $this->predicate;
            $sth = $this->DB->prepare($sql);
            $sth->execute($this->values);
            $rows = $sth->fetch(\PDO::FETCH_NUM);
            $this->count = $rows[0];
        }
        return $this->count;
    }


    function rewind() {
        echo "rewinding\n";
        reset($this->var);
    }
  
    function current() {
        $var = current($this->var);
        echo "current: $var\n";
        return $var;
    }
  
    function key() {
        $var = key($this->var);
        echo "key: $var\n";
        return $var;
    }
  
    function next() {
        $var = next($this->var);
        echo "next: $var\n";
        return $var;
    }
  
    function valid() {
        $key = key($this->var);
        $var = ($key !== NULL && $key !== FALSE);
        echo "valid: $var\n";
        return $var;
    }
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