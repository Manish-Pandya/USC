<?php
class Assert {
    private static $ASSERTION_LOG;

    public static function logAssertions(){
        Assert::$ASSERTION_LOG = array();
    }

    public static function getAssertions(){
        return Assert::$ASSERTION_LOG;
    }

    private static function __assert( $pass, $message ){
        // Log assertion, if enabled
        if( isset(Assert::$ASSERTION_LOG) ){
            Assert::$ASSERTION_LOG[] = array( $message, $pass );
        }

        // Use PHP internal assert
        assert( $pass, $message );
    }

    public static function eq( $actual, $expected, $message ){
        Assert::__assert( $actual == $expected, "$message (expected '$expected' | actual '$actual')");
    }

    public static function true( $val, $message ){
        Assert::__assert( $val == true, "$message (" . ($val ? 'true' : 'false') . ")");
    }

    public static function false( $val, $message ){
        Assert::__assert( $val == false, "$message (" . ($val ? 'true' : 'false') . ")");
    }

    public static function empty( $array, $message ){
        Assert::__assert( empty($array), "$message (size: " . count($array) . ")");
    }

    public static function not_empty( $array, $message ){
        Assert::__assert( !empty($array), "$message (size: " . count($array) . ")");
    }
}
?>
