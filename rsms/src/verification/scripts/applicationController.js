'use strict';

angular
    .module('applicationControllerModule',[])
    .factory('applicationControllerFactory', function applicationControllerFactory( modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods ){
        var ac = {};
        var store = dataStoreManager;
        //give us access to this factory in all views.  Because that's cool.
        $rootScope.af = this;

        store.$q = $q;


        return ac;
    });
