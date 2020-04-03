<?php
interface I_UserDetailProvider {
    function is_enabled();
    function getUserDetails( string $username );
}
?>
