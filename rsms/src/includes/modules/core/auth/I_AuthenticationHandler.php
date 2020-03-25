<?php
interface I_AuthenticationHandler {
    function is_enabled();
    function do_auth( string $name, string $secret );
}
?>
