<?php
class ConsoleTestReportWriter {

    public function write( Array $results ){

        function green($str){ return "\e[0;32m$str\e[0m"; }
        function red($str){ return "\e[0;31m$str\e[0m"; }
        function pass( $str = NULL ){ return green("[PASS]" . (isset($str) ? ": $str" : '')); }
        function fail( $str = NULL ){ return red("[FAIL]" . (isset($str) ? ": $str" : '')); }

        $passed = 0;
        foreach( $results as $testname => $testresults ){
            echo str_pad('+', strlen($testname) + 2, '-') . "\n";
            echo "|$testname:\n";
            $pass_count = 0;
            foreach( $testresults as $test => $res ){
                $passOrError = $res['pass'];
                $assertions = $res['assertions'];

                if( $passOrError === true ){
                    $pass_count++;
                }

                echo "+  $test: " . ($passOrError === true ? pass() : fail($passOrError)) . "\n";
                foreach( $assertions as $a ){

                    echo "|    " . ($a[1] ? pass($a[0]) : fail($a[0])) . "\n";
                }
            }

            if( $pass_count == count($testresults) ){
                $passed++;
            }

            echo "\n";
        }

        $total_tests = count($results);
        $percent = 0;
        if( $total_tests > 0 ){
            $percent = $passed / $total_tests * 100;
        }

        $summary = "$passed / " . count($results) . " Passed ($percent%)";
        $div = str_pad('', strlen($summary) + 2, '-');

        // Decorate summary string
        $summary = ($percent == 100 ? green($summary) : red($summary));

        echo "+$div+\n";
        echo "|" . str_pad('Test Results', strlen($div), ' ', STR_PAD_BOTH) . "\n";
        echo "| $summary\n";
        echo "+$div+\n\n";
    }
}
?>
