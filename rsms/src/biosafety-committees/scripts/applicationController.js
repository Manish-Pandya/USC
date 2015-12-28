'use strict';

angular
    .module('applicationControllerModule', ['rootApplicationController'])
    .factory('applicationControllerFactory', function applicationControllerFactory(modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods, rootApplicationControllerFactory) {
        var ac = rootApplicationControllerFactory;
        var store = dataStoreManager;
        //give us access to this factory in all views.  Because that's cool.
        store.$q = $q;

        ac.getAllPIs= function()
        {
            return this.getAllUsers()
                .then(
                    function(){
                        return dataSwitchFactory.getAllObjects('PrincipalInvestigator');
                    }
                )

        }

        ac.getAllUsers = function()
        {
            return dataSwitchFactory.getAllObjects('User');
        }

        ac.getAllHazards = function(){
            return dataSwitchFactory.getAllObjects('Hazard');
        }

        ac.getAllProtocols = function(){
            return dataSwitchFactory.getAllObjects('BiosafetyProtocol');
        }

        return ac;
    });
