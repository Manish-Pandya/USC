<?php
interface I_AuthorizationHandler {
    function is_enabled();
    function authorize( AuthenticationResult &$authentication );
}
?>
