<?php

class A {
	public $a = 1;
	public $b = 2;
	public $collection = array();

	function  __construct(){
		for ( $i=3; $i-->0;){
			array_push($this->collection, new B);
		}
	}
}

class B {
	public $a = 1;
	public $b = 2;
}

$json =  json_encode(new A());
echo '['.$json.']';
//echo '[{"id":"123","name":"Joe Doe"},{"id":"1","name":"John Smith"},{"id":"2","name":"Zoe Doe"}]';
?>
