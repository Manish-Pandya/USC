<?php

class CoreMessageMacros {

    public static function getResolvers(){
        $resolvers = array();

        // General
        $resolvers[] = new MacroResolver(
            null,
            '[RSMS Login]', 'URL of the RSMS Login page',
            function(){
                $urlBase = ApplicationConfiguration::get('server.web.url');
                $loginPath = ApplicationConfiguration::get('server.web.LOGIN_PAGE', '/rsms');
                return "$urlBase$loginPath";
            }
        );

        return $resolvers;
    }

}

?>