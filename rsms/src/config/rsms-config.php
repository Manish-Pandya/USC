<?php
return array(
    "server" => array(
        "auth" => array(
            "type" => "dev",
        ),

        "db" => array(
            "connection" => 'mysql:host=localhost;dbname=usc_ehs_rsms',
            "username" => 'root',
            "password" => '',
        ),

        "web" => array(
            "ADMIN_MAIL" => 'mmartin@graysail.com',
            "WEB_ROOT" => '/rsms/src/',
            "LOGIN_PAGE" => '/rsms/src/',
            "BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR" => '~/rsmsuploads/',
        )
    ),
    "logging"=>array(
        "file" => "/includes/conf/log4php-config.php",
    )
);
?>