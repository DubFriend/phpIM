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
        $Results = $this->DB->prepare($statement);
        $Results->execute($values);//->execute($values);
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
            $count,
            $isNextCalled = false,
            $key = -1,
            $current;

    function __construct(array $fig = array()) {
        $this->Results = $fig['results'];
        $this->predicate = $fig['predicate'];
        $this->values = $fig['values'];
        $this->DB = $fig['connection'];

        $this->Results->setFetchMode(PDO::FETCH_ASSOC);
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
        if($this->isNextCalled) {
            throw new Exception("Rewind not supported by Sequel_Results");
        }
    }
  
    function current() {
        if($this->current) {
            return $this->current;
        }
        else {
            return $this->next();
        }
    }
  
    function key() {
        return $this->key;
    }
  
    function next() {
        $this->isNextCalled = true;
        $this->key += 1;
        $this->current = $this->Results->fetch();
        if($this->current !== false) {
            return $this->current;
        }
    }
  
    function valid() {
        if($this->key === -1) {
            $this->next();
        }
        if($this->current) {
            return true;
        }
        else {
            return false;
        } 
    }
}
?>