<?php
return array(
    // Authentication settings
    "server.auth.enable_dev_logins" => true,
    //"server.auth.include_script" => "",

    // DB Settings
    "server.db.connection" => 'mysql:host=localhost;dbname=usc_ehs_rsms',
    "server.db.username" => 'root',
    //"server.db.password" => '',

    // Web-server settings
    "server.web.ADMIN_MAIL" => 'mmartin@graysail.com',
    "server.web.WEB_ROOT" => '/rsms/src/',
    "server.web.LOGIN_PAGE" => '/rsms/src/',
    "server.web.BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR" => '~/rsmsuploads/',

    // Logging settings
    "logging.configfile" => "./includes/conf/log4php-config.php",
    "logging.outputdir" => "./logs"
);
?>