<?php
interface I_AuthorizationHandler {
    function is_enabled();
    function type();
    function authorize( AuthenticationResult &$authentication );
}
?>
