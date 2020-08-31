<?php

class CoreMessageMacros {

    public static function getResolvers(){
        $resolvers = array();

        // General
        $resolvers[] = new MacroResolver(
            null,
            '[RSMS Login]', 'URL of the RSMS Login page',
            function(){
                $urlBase = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_WEB_URL);
                $loginPath = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_WEB_LOGIN_PAGE, '/rsms');
                return "$urlBase$loginPath";
            }
        );

        // My Lab
        $resolvers[] = new MacroResolver(
            null,
            '[My Lab]', 'URL of the My Lab dashboard',
            function(){
                $urlBase = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_WEB_URL);
                $mylablink = $urlBase . WEB_ROOT . 'views/lab/mylab.php';

                return $mylablink;
            }
        );

        return $resolvers;
    }

}

?>