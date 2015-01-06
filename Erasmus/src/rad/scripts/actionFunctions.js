'use strict';

angular
    .module('actionFunctionsModule',[])
    
        .factory('actionFunctionsFactory', function actionFunctionsFactory( modelInflatorFactory, genericAPIFactory, $rootScope, $q ){
        	var af = {};
            var store = dataStoreManager;


            /********************************************************************
            **
            **      CLIENT MANAGEMENT CONVENIENCE
            **
            ********************************************************************/

            af.copy = function( object )
            {
                    store.createCopy( object );
                    //set the other objects in this one's collection to the non-edit state
                    store.setEditStates( object );

            }

            af.cancelEdit = function( object )
            {
                    object.Edit = false;
                    store.replaceWithCopy( object );
            }

            af.setObjectActiveState = function( object )
            {

                    object.setIs_active( !object.Is_active );

                    //set a root scope marker as the promise so that we can use angular-busy directives in the view
                    $rootScope[object.Class+'Saving'] = genericAPIFactory.save( object )
                        .then(
                            function( returnedPromise ){
                                console.log(returnedPromise);
                                
                                if(typeof returnedPromise === 'object')object = returnedPromise;
                                return true;
                            },
                            function( error )
                            {
                                //object.Name = error;
                                object.setIs_active( !object.Is_active );
                                $rootScope.error = 'error';
                            }
                        );
                    return object;

            }

            af.saveObject = function( object )
            {
                    //set a root scope marker as the promise so that we can use angular-busy directives in the view
                    $rootScope[object.Class+'Saving'] = genericAPIFactory.save( object )
                        .then(
                            function( returnedPromise ){
                                console.log(returnedPromise);          
                                if(typeof returnedPromise.data === 'object') {
                                        for( var prop in returnedPromise.data ){
                                                object[prop] = returnedPromise.data[prop];
                                        }
                                }
                                object.Edit = false;
                            },
                            function( error )
                            {
                                //object.Name = error;
                               // object.setIs_active( !object.Is_active );
                                $rootScope.error = 'error';
                            }
                        );
                    return object;
            }


        	/********************************************************************
        	**
        	**		USER MANAGEMENT
        	**
        	********************************************************************/

        	af.getUserById = function( key_id )
        	{

        			var urlSegment = 'getUserById&id=' + key_id;

                    if( store.checkCollection( 'User', key_id ) ){
                        var user = store.getById( 'User', key_id )
                            .then(
                                function( user ){
                                    return user;
                                }
                            );
                    }else{
            			var user = genericAPIFactory.read( urlSegment )
    	        			.then(
    	        				function( returnedPromise ){
    	        					return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
    	        				}
    	        			);
                    }
        			return user;
        	}

        	af.getAllUsers = function()
        	{
        			var urlSegment = 'getAllUsers';

                    if( store.checkCollection( 'Users' ) ){
                        var users = store.get( 'Users' )
                            .then(
                                function( users ){
                                    return users;
                                }
                            );
                    }else{
                        console.log('here');
                        var users = genericAPIFactory.read( urlSegment )
                            .then(
                                function( returnedPromise ){
                                    //console.log(returnedPromise.data)
                                    //var users = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                    store.store( returnedPromise.data );
                                    //console.log(store.get( 'Users' ));
                                    return store.get( 'Users' );
                                }
                            ); 
                    }

        			return users;
        	}

            af.getUsersViewModel = function()
            {       
                    var model = [];
                    var userPromise = $q.defer();
                    var piPromise = $q.defer();
                    var roomsPromise = $q.defer();
                    var relationsPromise = $q.defer();
                    var all = $q.all([userPromise.promise,relationsPromise.promise,piPromise.promise,roomsPromise.promise])

                    this.getAllUsers()
                        .then(
                            function(users){
                                console.log('here now')
                                userPromise.resolve(users);
                            }

                        )

                    this.getAllPIRoomRelations()
                        .then(
                            function(relations){
                                relationsPromise.resolve(relationsPromise);
                            }
                        )

                    this.getAllPIs()
                        .then(
                            function (pis){
                                console.log('and now here');
                                piPromise.resolve(pis);
                            }
                        )

                    this.getAllRooms()
                        .then(
                            function(rooms){
                                roomsPromise.resolve(rooms);
                            }
                        )

                    return all.then(
                                function( model ){
                                    var inflatedModel = {}
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'Users' ) ) );
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'PrincipalInvestigators' ) ) );
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'PrincipalInvestigatorRoomRelations' ) ) );
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'Rooms' ) ) );

                                    inflatedModel.users = modelInflatorFactory.callAccessors( 'Users' );
                                    inflatedModel.pis = modelInflatorFactory.callAccessors( 'PrincipalInvestigators' );
                                    inflatedModel.relations = modelInflatorFactory.callAccessors( 'PrincipalInvestigatorRoomRelations' );
                                    inflatedModel.rooms = modelInflatorFactory.callAccessors( 'Rooms' );

                                    return inflatedModel;
                                }
                            )
            }


            /********************************************************************
            **
            **      HAZARD MANAGEMENT
            **
            ********************************************************************/

            af.getAllHazards = function()
            {

                    var urlSegment = 'getAllHazards';

                    if( store.checkCollection( 'Hazards' ) ){
                            console.log('check positive');
                            var hazards = $q.defer()
                            var storedHazards = store.get( 'Hazards' );
                            hazards.resolve(storedHazards);
                            return hazards.promise;
                    }else{
                            var hazards = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        console.log(returnedPromise);
                                        var hazards = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( hazards );
                                        return store.get( 'Hazards' );
                                    }
                                );  
                    }                    
                    return hazards;
            }

            af.getHasChildren = function( hazards )
            {
                    var i = hazards.length;
                    while(i--){
                        if( hazards[i].Parent_hazard_id ){
                            var parent = store.getById( 'Hazard',hazards[i].Parent_hazard_id );
                            parent.setHasChildren(true);
                        }
                    }

            }

            af.getHazardNode = function( nodeId )
            {
                    var urlSegment = 'getHazardTreeNode&id='+nodeId;

                    if(nodeId == 10000){
                        if( store.checkCollection( 'Hazards', nodeId ) ){
                            console.log('check positive');
                            var hazards = store.get( 'Hazards' )
                                .then(
                                    function( hazards ){
                                        console.log('here');
                                        console.log(hazards);
                                        return hazards;
                                    }
                                );
                        }else{
                            console.log('here')
                            var hazards = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        var hazards = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( hazards );
                                        return store.get( 'Hazards' )
                                            .then(
                                                function( hazards ){
                                                    console.log('adfasdfasdf');
                                                    return hazards;
                                                }
                                            );
                                    }
                                );  
                        }
                    }                        
                    return hazards;
            }

            af.getHazardNodeFromCache = function(id)
            {
                    return store.getChildrenByParentProperty('Hazards', 'Pa')
            }

            af.setHazardActiveState = function( hazard )
            {
                    af.setActiveState( hazard );
            }

            af.saveHazard = function( hazard )
            {

            }

            af.moveHazard = function(idx, hazard, direction, filteredSubHazards)
            {

                    var parent = store.getById( 'Hazard', hazard.getParent_hazard_id() );

                    //get the other, or other two hazards we need
                    //Make a copy of the hazard we want to move, so that it can be temporarily moved in the view
                    filteredSubHazards[idx].IsDirty = true;
                    if(direction == 'up'){
                        //We are moving a hazard up. Get the indices of the two hazards above it.
                        var afterHazardIdx = idx-1;
                        var beforeHazardIdx = idx-2;
                    }else if(direction == 'down'){
                        //We are moving a hazard down.  Get the indices of the two hazards below it.
                        var beforeHazardIdx = idx+1;
                        var afterHazardIdx = idx+2;
                    }else{
                        return
                    }

                    //get the key_ids of the hazards involved so we can build the request.
                    var hazardId       = filteredSubHazards[idx].Key_id;

                    //if we are moving the hazard up to the first spot, the index for the before hazard will be - 1, so we can't get a key_id
                    if(beforeHazardIdx > -1){
                        var beforeHazardId = filteredSubHazards[beforeHazardIdx].Key_id;
                    }else{
                        var beforeHazardId = null
                    }

                    //if we are moving the hazard down to the last spot, the index for the before hazard will out of range, so we can't get a key_id
                    if(afterHazardIdx < filteredSubHazards.length){
                        var afterHazardId = filteredSubHazards[afterHazardIdx].Key_id;  
                    }else{
                        var afterHazardId = null;
                    }

                    var url = 'reorderHazards&hazardId='+hazardId+'&beforeHazardId='+beforeHazardId+'&afterHazardId='+afterHazardId;

                    $rootScope.HazardSaving = genericAPIFactory.save( hazard, url )
                        .then(
                            function( returned ){
                                console.log( returned.data );
                                hazard.setOrder_index( returned.data );
                            },
                            function( error )
                            {
                                //object.Name = error;
                                $rootScope.error = hazard.Name + ' couldn\'t be moved.';
                            }
                        );                    

            }



            /********************************************************************
            **
            **      PRINCIPAL INVESTIGATOR
            **
            ********************************************************************/

            af.onSelectPi = function(pi)
            {
                    console.log(pi);
                    $rootScope.pi = pi;
                    console.log($rootScope);
            }

            af.getAllPIs= function()
            {
                    var urlSegment = 'getAllPIs';

                    if( store.checkCollection( 'PrincipalInvestigators' ) ){
                            var pis = $q.defer()
                            var storedPIs = store.get( 'PrincipalInvestigators' );
                            pis.resolve(storedPIs);
                            return pis.promise
                    }else{
                            var pis = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        //var pis = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( returnedPromise.data );
                                        return store.get( 'PrincipalInvestigators' );
                                    }
                                );  
                    }                    
                    return pis;
            }

            af.getAllPIRoomRelations = function()
            {
                    var urlSegment = 'getAllPrincipalInvestigatorRoomRelations';

                    if( store.checkCollection( 'PrincipalInvestigatorRoomRelations' ) ){
                            var relations = $q.defer()
                            var storedRelations = store.get( 'PrincipalInvestigatorRoomRelations' );
                            relations.resolve(storedRelations);
                            return relations.promise
                    }else{
                            var relations = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        var returnedRelations = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( returnedRelations );
                                        return store.get( 'PrincipalInvestigatorRoomRelations' );
                                    }
                                );  
                    }                    
                    return relations;
            }


            /********************************************************************
            **
            **      HAZARD INVENTORY
            **
            ********************************************************************/

            af.setInspectionRooms = function( pi )
            {
                    store.store( pi.rooms, false );
            }

            af.setInpsection = function( PIKeyID, inspectorIds, inspectionId )
            {

                    //set inspectionId to empty strying if we are starting a new inspection
                    if(!inspectionId)inspectionId = '';

                    var url = 'initiateInspection&piId='+PIKeyID+'&'+$.param({inspectorIds:inspectorIds})+'&inspectionId='+inspectionId;
                    $rootScope.inpsectionPromise = genericAPIFactory.read(url)
                        .then(
                            function( inspection ){
                                store.store( inspection );
                                return store.get( 'Inspection' )
                            },
                            function(promise){
                                
                            }
                        );  
                    return $rootScope.inpsectionPromise;
            }

            af.resetInspectionRooms = function( roomIds, inspectionId )
            {

                    //we have changed the room collection for this inspection, so we set the new relationships on the server and get back and new collection of hazards  
                    var url = 'resetInspectionRooms&inspectionId='+inspectionId+'&'+$.param({roomIds:roomIds})+'&callback=JSON_CALLBACK';

                    $rootScope.inpsectionPromise = genericAPIFactory.read( url )
                            .then(
                                function( inspection ){
                                    return inspection
                                },
                                function(promise){
                                } 
                            );  
                    return $rootScope.inpsectionPromise;
                
            }

            af.getHazardRoomRelations = function( pi )
            {
                    var rooms = pi.getRooms();
                    var i = rooms.length;
                    var roomIds = [];

                    while(i--){
                        roomIds.push(rooms[i].Key_id);
                    }

                    //we have changed the room collection for this inspection, so we set the new relationships on the server and get back and new collection of hazards  
                    var url = 'getHazardRoomRelations&'+$.param({roomIds:roomIds})+'&callback=JSON_CALLBACK';

                    $rootScope.inpsectionPromise = genericAPIFactory.read( url )
                            .then(
                                function( HazardRoomRelations ){
                                    store.store( HazardRoomRelations, true );
                                    return HazardRoomRelations
                                },
                                function(promise){
                                } 
                            );  
                    return $rootScope.inpsectionPromise;
            }

            /********************************************************************
            **
            **      LOCATIONS (Buildings, Rooms, Campuses)
            **
            ********************************************************************/

            af.getAllRooms = function()
            {
                    var urlSegment = 'getAllRooms';

                    if( store.checkCollection( 'Rooms' ) ){
                            var rooms = $q.defer()
                            var storedRooms = store.get( 'Rooms' );
                            relations.resolve(storedRooms);
                            return rooms.promise
                    }else{
                            var rooms = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        var returnedRooms = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( returnedRooms );
                                        return store.get( 'Rooms' );
                                    }
                                );  
                    }                    
                    return rooms;
            }

            af.test = function(user)
            {
                    dataStoreManager.getById("User", user.Key_id).setName('updated');
                    //user.Supervisor.User.setName('updated');
                    console.log(dataStoreManager.getById("PrincipalInvestigator", user.Supervisor_id))
;            }

        	return af;
		});
