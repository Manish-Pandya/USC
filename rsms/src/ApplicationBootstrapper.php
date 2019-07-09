<?php

/**
 * Utility class responsible for bootstrapping RSMS based on
 * application configuration.
 */
class ApplicationBootstrapper {
    /* Application Configuration Parameter Names */
    // Logging
    public const CONFIG_LOGGING_OUTPUTDIR = 'logging.outputdir';
    public const CONFIG_LOGGING_CONFIGFILE = 'logging.configfile';

    // Authentication/Authorization
    public const CONFIG_SERVER_AUTH_INCLUDE = 'server.auth.include_script';
    public const CONFIG_SERVER_AUTH_PROVIDE_LDAP = 'server.auth.providers.ldap';

    public const CONFIG_SERVER_AUTH_PROVIDE_EMERGENCY = 'server.auth.providers.emergency';
    public const CONFIG_SERVER_AUTH_PROVIDE_EMERGENCY_PASSWORD = 'server.auth.providers.emergency.password';

    public const CONFIG_SERVER_AUTH_PROVIDE_DEV_ROLE = 'server.auth.providers.dev.role';
    public const CONFIG_SERVER_AUTH_PROVIDE_DEV_ROLE_PASSWORD = 'server.auth.providers.dev.role.password';

    public const CONFIG_SERVER_AUTH_PROVIDE_DEV_IMPERSONATE = 'server.auth.providers.dev.impersonate';
    public const CONFIG_SERVER_AUTH_PROVIDE_DEV_IMPERSONATE_PASSWORD = 'server.auth.providers.dev.impersonate.password';

    // Server Environment
    public const CONFIG_SERVER_ENV_NAME = 'server.env.name';
    public const CONFIG_SERVER_ENV_SHOW_DETAILS = 'server.env.display_details';
    public const CONFIG_SERVER_ENV_SHOW_APP_VERSION = 'server.env.display_version';
    public const CONFIG_SERVER_ENV_SHOW_PHP_VERSION = 'server.env.display_php_version';
    public const CONFIG_SERVER_ENV_STYLE = 'server.env.style';

    // Webserver
    public const CONFIG_SERVER_WEB_URL = 'server.web.url';
    public const CONFIG_SERVER_WEB_ADMIN_MAIL = 'server.web.ADMIN_MAIL';
    public const CONFIG_SERVER_WEB_WEB_ROOT = 'server.web.WEB_ROOT';
    public const CONFIG_SERVER_WEB_LOGIN_PAGE = 'server.web.LOGIN_PAGE';
    public const CONFIG_SERVER_WEB_BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR = 'server.web.BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR';
    public const CONFIG_SERVER_WEB_HELP_CONTACT_USERNAME = 'server.web.HELP_CONTACT_USERNAME';

    // DB
    public const CONFIG_SERVER_DB_HOST = 'server.db.host';
    public const CONFIG_SERVER_DB_NAME = 'server.db.name';
    public const CONFIG_SERVER_DB_USERNAME = 'server.db.username';
    public const CONFIG_SERVER_DB_PASSWORD = 'server.db.password';
    public const CONFIG_SERVER_DB_CONNECTION = 'server.db.connection';
    /*********************************************/

    private static $BOOTSTRAP_PATH;
    private static $bootstrapping_processing = false;
    private static $bootstrapping_complete = false;

    /**
     * Executes the RSMS Bootstrap Process.
     *
     * @param string $overrideAppConfig Optional path to a configurartion file
     *  which should be used in lieu of the standard path
     *
     * @return void
     */
    public static function bootstrap( $overrideAppConfig = NULL, $mergeOverrides = NULL ){
        if( self::$bootstrapping_complete || self::$bootstrapping_processing ){
            // Ignore subsequent calls
            return;
        }

        self::$bootstrapping_processing = true;

        ////////////////////////////////////////////
        // Note the current path for bootstrapping
        self::$BOOTSTRAP_PATH = dirname(__FILE__);

        ////////////////////////////////////////////
        // Read application config before all else
        require_once self::$BOOTSTRAP_PATH . '/ApplicationConfiguration.php';
        ApplicationConfiguration::configure( $overrideAppConfig, $mergeOverrides );

        ////////////////////////////////////////////
        // Set up Logging with config parameters
        ApplicationBootstrapper::init_logging(
            ApplicationConfiguration::get( ApplicationBootstrapper::CONFIG_LOGGING_OUTPUTDIR, './logs'),
            ApplicationConfiguration::get( ApplicationBootstrapper::CONFIG_LOGGING_CONFIGFILE )
        );

        ////////////////////////////////////////////
        // Enable Autoloading
        require_once(self::$BOOTSTRAP_PATH . '/Autoloader.php');

        ////////////////////////////////////////////
        // LDAP Authentication
        ApplicationBootstrapper::init_authentication(
            ApplicationConfiguration::get( ApplicationBootstrapper::CONFIG_SERVER_AUTH_INCLUDE )
        );

        ////////////////////////////////////////////
        // Environment Constants
        ApplicationBootstrapper::init_app_environment();

        ////////////////////////////////////////////
        // Error-handling
        ApplicationBootstrapper::init_error_handling();

        ////////////////////////////////////////////
        // Module Registration
        ApplicationBootstrapper::register_modules();

        ////////////////////////////////////////////
        // Bootstrapping complete
        self::$bootstrapping_processing = false;
        self::$bootstrapping_complete = true;
    }

    /**
     * Utility function which converts the given path to
     * an absolute (if it is not already). If the path is transformed,
     * it is made absolute based on our current BOOTSTRAP_PATH
     *
     * @param string $path Path to transform to absolute
     *
     * @return string Absolute version of $path
     */
    private static function getLocalPath( $path ){
        // If this is not an absolute path, prefix it with our path
        if( $path != NULL && substr($path, 0, 1) !== '/' ){
            return self::$BOOTSTRAP_PATH . "/$path";
        }

        return $path;
    }

    /**
     * Set up Logging
     *
     * @param string $logs_root Path to diretory where Log files should be placed
     * @param string $configFilePath Path to log configuration file
     *
     * @return void
     */
    private static function init_logging( $logs_root, $configFilePath ){
        // Require Logger API
        require_once self::$BOOTSTRAP_PATH . '/logging/Logger.php';

        // Define constant pointing to logs path (so log config can use it)
        define('RSMS_LOGS', self::getLocalPath($logs_root));

        // Configure logging
        Logger::configure( self::getLocalPath( $configFilePath ));
    }

    /**
     * Includes non-sourced authentication dependency, if provided
     *
     * This allows support for intermediary authentication handler.
     *
     * @param string $auth_provider_include Absolute path to include script
     *
     * @return void
     */
    private static function init_authentication( $auth_provider_include ){
        // USER AUTHENTICATION AND AUTHORIZATION
        // Load non-sourced script intended for per-instance specification of (LDAP) auth provider
        if( $auth_provider_include ){
            $authLog = Logger::getLogger('auth_provider');
            if( $authLog->isTraceEnabled()){
                $authLog->trace("Load auth provider script: $auth_provider_include");
            }

            require_once( self::getLocalPath( $auth_provider_include ));
        }
    }

    /**
     * Sets environment constants
     *
     * @return void
     */
    private static function init_app_environment(){
        //  Application environment-dependent Constants
        define('ADMIN_MAIL', ApplicationConfiguration::get( ApplicationBootstrapper::CONFIG_SERVER_WEB_ADMIN_MAIL));
        define('WEB_ROOT', ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_WEB_WEB_ROOT));
        define('LOGIN_PAGE', ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_WEB_LOGIN_PAGE));
        define('BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR', ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_WEB_BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR));

        // Read Version Info
        define('RSMS_ENV_DETAILS', self::read_version_details());
    }

    /**
     * Utility function which collects version details for display, if configured
     *
     * This function reads environment properties from application config and constructs
     * a descriptor string
     *
     * @return string Version detail string
     */
    private static function read_version_details(){
        if( ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_ENV_SHOW_DETAILS, false) ){
            $details = array();

            $serverName = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_ENV_NAME, '');
            if( $serverName ){
                $details[] = $serverName;
            }

            if( ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_ENV_SHOW_APP_VERSION, false) ){
                $versionFile = self::$BOOTSTRAP_PATH . '/version';
                if( file_exists($versionFile)){
                    $details[] = @file_get_contents($versionFile);
                }
            }

            if( ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_ENV_SHOW_PHP_VERSION, false) ){
                $details[] = 'PHP ' . phpversion();
            }

            return implode(' | ', $details);
        }

        return '';
    }

    /**
     * Initialize application Error handling
     *
     * @return void
     */
    private static function init_error_handling(){
        ErrorHandler::init();
    }

    /**
     * Register application Modules
     *
     * @return void
     */
    private static function register_modules(){
        ModuleManager::registerModules();
    }
}

?>
