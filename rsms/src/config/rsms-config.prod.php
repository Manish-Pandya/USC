<?php
return array(
    "server" => array(
        "auth" => array(
            "type" => "ldap",
            "provider" => '/usr/local/src/csg/classes/ADLDAPV2.php'
        ),

        "db" => array(
            "connection" => 'mysql:host=localhost;dbname=usc_ehs_rsms'
            "username" => 'erasmus',
            "password" => 'eR@m#682d'
        ),

        "web" => array(
            "ADMIN_MAIL" => 'mmartin@graysail.com',
            "WEB_ROOT" => '/rsms/',
            "LOGIN_PAGE" => '/rsms/',
            "BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR" => , getcwd().'/biosafety-committees/protocol-documents/'
        )
    ),
    "logging"=>array(
        "file" => "/includes/conf/log4php-config.php"
    ),
);
?>