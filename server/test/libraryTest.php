<?php
require_once 'library.php';

class Library_Test extends PHPUnit_Framework_TestCase {
	function test_remove_trailing() {
		$this->assertEquals("base", remove_trailing("basestring", "string"));
	}

	function test_remove_trailing_no_match() {
		$this->assertEquals("basestring", remove_trailing("basestring", "no_match"));
	}
}