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

        ac.getAllHazardDtos = function(id, roomId){

            var urlSegment = "getHazardRoomDtosByPIId&id="+id;
            if(roomId) urlSegment = urlSegment +"room="+roomId;

            return genericAPIFactory.read( urlSegment )
                    .then(
                        function( returnedPromise ){
                            var hazards = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( hazards );
                            return store.get( 'HazardDto' );
                        }
                    );
        }
        
        ac.handleHazardChecked = function(hazardDto){
            this.clearError();
            this.save(hazardDto)
                .then(
                    function(){},
                    function(){
                        hazardDto.isPresent = !hazardDto.isPresent;
                        this.setError("Something went wrong.");
                    }
                )
        }
        
        ac.saveHazardDto = function(){
        }

        return ac;
    });
