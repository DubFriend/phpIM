<?php
require_once 'library.php';

class Library_Test extends PHPUnit_Framework_TestCase {

    
    
    function test_remove_trailing() {
        $this->assertEquals("base", remove_trailing("basestring", "string"));
    }

    function test_remove_trailing_no_match() {
        $this->assertEquals("basestring", remove_trailing("basestring", "no_match"));
    }

    function test_array_by_column() {
        $this->assertEquals(
            array("foo", null, "bar"),
            array_by_column(array(
                array("a" => "foo"),
                array("b" => "wrong key"),
                array("a" => "bar")
            ), 'a')
        );
    }

    function test_nested_block_string_positions() {
        $this->assertEquals(
            array(2,16),
            nested_block_string_positions("ab{c{{d}{ }}e{}f}gh", "{", "}")
        );
    }

    function test_nested_block_string_positions_multi_character() {
        $this->assertEquals(
            array(1,10),
            nested_block_string_positions("a#begin098#end", "#begin", "#end")
        );
    }

    function test_nested_block_string_positions_not_found() {
        $this->assertEquals(
            false,
            nested_block_string_positions("notfoun}d", "{", "}")
        );
    }
    
    function test_nested_block_no_ending() {
        $this->assertEquals(
            array(0, null),
            nested_block_string_positions("{openonly", "{", "}")
        );
    }
}
