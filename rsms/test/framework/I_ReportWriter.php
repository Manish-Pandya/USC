<?php
interface I_ReportWriter {
    function writePhase( $name );
    function writePhaseProgress( $name );
    function writePhaseEnd();
    function writeReport( Array $results );
}
?>
