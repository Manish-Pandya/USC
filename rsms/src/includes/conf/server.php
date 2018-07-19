<?php

// Additional includes, as necessary
if( ApplicationConfiguration::get()['server']['auth']['type'] === "ldap" ){
    require_once(ApplicationConfiguration::get()['server']['auth']['provider']);
}

/*DEPRECATED?*/
function isProductionServer() {
    return false;
}

function getDBConnection() {
    return ApplicationConfiguration::get()['server']['db']['connection'];
}

function getDBUsername() {
    return ApplicationConfiguration::get()['server']['db']['username'];
}

function getDBPassword() {
    return ApplicationConfiguration::get()['server']['db']['password'];
}

?>
