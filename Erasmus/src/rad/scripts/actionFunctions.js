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

            af.getById = function( objectFlavor, key_id )
            {
                    return store.getById(objectFlavor, key_id );       
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

            af.getHazardById = function( key_id )
            {
                var urlSegment = 'getHazardById&id=' + key_id;

                if( store.checkCollection( 'Hazard', key_id ) ) {
                    var hazard = store.getById( 'Hazard', key_id )
                        .then(function(hazard) {
                            return hazard;
                        });
                }
                else {
                    var hazard = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store isotope in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return hazard;
            }


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
            **      ISOTOPE
            **
            ********************************************************************/

            af.getIsotopeById = function( key_id )
            {
                var urlSegment = 'getIsotopeById&id=' + key_id;

                if( store.checkCollection( 'Isotope', key_id ) ) {
                    var isotope = store.getById( 'Isotope', key_id )
                        .then(function(isotope) {
                            return isotope;
                        });
                }
                else {
                    var isotope = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store isotope in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return isotope;
            }

            af.getAllIsotopes = function( key_id )
            {
                var urlSegment = 'getAllIsotopes';

                if( store.checkCollection('Isotopes') ) {
                    var isotopes = store.get( 'Isotopes' ).then(function(isotope) {
                        return isotope;
                    });
                }
                else {
                    var isotopes = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var isotopes = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( isotopes );
                            return store.get( 'Isotopes' );
                        });
                }
                return isotopes;
            }


            /********************************************************************
            **
            **      AUTHORIZATION            **
            ********************************************************************/

            af.getAuthorizationById = function( key_id )
            {
                var urlSegment = 'getAuthorizationById&id=' + key_id;

                if( store.checkCollection( 'Authorization', key_id ) ) {
                    var authorization = store.getById( 'Authorization', key_id )
                        .then(function(authorization) {
                            return authorization;
                        });
                }
                else {
                    var authorization = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store authorization in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return authorization;
            }

            af.getAllAuthorizations = function( key_id )
            {
                var urlSegment = 'getAllAuthorizations';

                if( store.checkCollection('Authorizations') ) {
                    var authorizations = store.get( 'Authorizations' ).then(function(authorization) {
                        return authorization;
                    });
                }
                else {
                    var authorizations = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var authorizations = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( authorizations );
                            return store.get( 'Authorizations' );
                        });
                }
                return authorizations;
            }



            /********************************************************************
            **
            **      CARBOY            **
            ********************************************************************/

            af.getCarboyById = function( key_id )
            {
                var urlSegment = 'getCarboyById&id=' + key_id;

                if( store.checkCollection( 'Carboy', key_id ) ) {
                    var carboy = store.getById( 'Carboy', key_id )
                        .then(function(carboy) {
                            return carboy;
                        });
                }
                else {
                    var carboy = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store carboy in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return carboy;
            }

            af.getAllCarboys = function( key_id )
            {
                var urlSegment = 'getAllCarboys';

                if( store.checkCollection('Carboys') ) {
                    var carboys = store.get( 'Carboys' ).then(function(carboy) {
                        return carboy;
                    });
                }
                else {
                    var carboys = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var carboys = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( carboys );
                            return store.get( 'Carboys' );
                        });
                }
                return carboys;
            }



            /********************************************************************
            **
            **      DRUM            **
            ********************************************************************/

            af.getDrumById = function( key_id )
            {
                var urlSegment = 'getDrumById&id=' + key_id;

                if( store.checkCollection( 'Drum', key_id ) ) {
                    var drum = store.getById( 'Drum', key_id )
                        .then(function(drum) {
                            return drum;
                        });
                }
                else {
                    var drum = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store drum in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return drum;
            }

            af.getAllDrums = function( key_id )
            {
                var urlSegment = 'getAllDrums';

                if( store.checkCollection('Drums') ) {
                    var drums = store.get( 'Drums' ).then(function(drum) {
                        return drum;
                    });
                }
                else {
                    var drums = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var drums = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( drums );
                            return store.get( 'Drums' );
                        });
                }
                return drums;
            }



            /********************************************************************
            **
            **      PARCEL            **
            ********************************************************************/

            af.getParcelById = function( key_id )
            {
                var urlSegment = 'getParcelById&id=' + key_id;

                if( store.checkCollection( 'Parcel', key_id ) ) {
                    var parcel = store.getById( 'Parcel', key_id )
                        .then(function(parcel) {
                            return parcel;
                        });
                }
                else {
                    var parcel = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store parcel in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return parcel;
            }

            af.getAllParcels = function( key_id )
            {
                var urlSegment = 'getAllParcels';

                if( store.checkCollection('Parcels') ) {
                    var parcels = store.get( 'Parcels' ).then(function(parcel) {
                        return parcel;
                    });
                }
                else {
                    var parcels = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var parcels = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( parcels );
                            return store.get( 'Parcels' );
                        });
                }
                return parcels;
            }



            /********************************************************************
            **
            **      PARCELUSE            **
            ********************************************************************/

            af.getParcelUseById = function( key_id )
            {
                var urlSegment = 'getParcelUseById&id=' + key_id;

                if( store.checkCollection( 'ParcelUse', key_id ) ) {
                    var parceluse = store.getById( 'ParcelUse', key_id )
                        .then(function(parceluse) {
                            return parceluse;
                        });
                }
                else {
                    var parceluse = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store parceluse in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return parceluse;
            }

            af.getAllParcelUses = function( key_id )
            {
                var urlSegment = 'getAllParcelUses';

                if( store.checkCollection('ParcelUses') ) {
                    var parceluses = store.get( 'ParcelUses' ).then(function(parceluse) {
                        return parceluse;
                    });
                }
                else {
                    var parceluses = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var parceluses = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( parceluses );
                            return store.get( 'ParcelUses' );
                        });
                }
                return parceluses;
            }



            /********************************************************************
            **
            **      PARCELUSEAMOUNT            **
            ********************************************************************/

            af.getParcelUseAmountById = function( key_id )
            {
                var urlSegment = 'getParcelUseAmountById&id=' + key_id;

                if( store.checkCollection( 'ParcelUseAmount', key_id ) ) {
                    var parceluseamount = store.getById( 'ParcelUseAmount', key_id )
                        .then(function(parceluseamount) {
                            return parceluseamount;
                        });
                }
                else {
                    var parceluseamount = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store parceluseamount in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return parceluseamount;
            }

            af.getAllParcelUseAmounts = function( key_id )
            {
                var urlSegment = 'getAllParcelUseAmounts';

                if( store.checkCollection('ParcelUseAmounts') ) {
                    var parceluseamounts = store.get( 'ParcelUseAmounts' ).then(function(parceluseamount) {
                        return parceluseamount;
                    });
                }
                else {
                    var parceluseamounts = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var parceluseamounts = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( parceluseamounts );
                            return store.get( 'ParcelUseAmounts' );
                        });
                }
                return parceluseamounts;
            }



            /********************************************************************
            **
            **      PICKUP            **
            ********************************************************************/

            af.getPickupById = function( key_id )
            {
                var urlSegment = 'getPickupById&id=' + key_id;

                if( store.checkCollection( 'Pickup', key_id ) ) {
                    var pickup = store.getById( 'Pickup', key_id )
                        .then(function(pickup) {
                            return pickup;
                        });
                }
                else {
                    var pickup = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store pickup in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return pickup;
            }

            af.getAllPickups = function( key_id )
            {
                var urlSegment = 'getAllPickups';

                if( store.checkCollection('Pickups') ) {
                    var pickups = store.get( 'Pickups' ).then(function(pickup) {
                        return pickup;
                    });
                }
                else {
                    var pickups = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var pickups = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( pickups );
                            return store.get( 'Pickups' );
                        });
                }
                return pickups;
            }



            /********************************************************************
            **
            **      PRINCIPALINVESTIGATOR            **
            ********************************************************************/

            af.getPrincipalInvestigatorById = function( key_id )
            {
                var urlSegment = 'getPrincipalInvestigatorById&id=' + key_id;

                if( store.checkCollection( 'PrincipalInvestigator', key_id ) ) {
                    var principalinvestigator = store.getById( 'PrincipalInvestigator', key_id )
                        .then(function(principalinvestigator) {
                            return principalinvestigator;
                        });
                }
                else {
                    var principalinvestigator = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store principalinvestigator in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return principalinvestigator;
            }

            af.getAllPrincipalInvestigators = function( key_id )
            {
                var urlSegment = 'getAllPrincipalInvestigators';

                if( store.checkCollection('PrincipalInvestigators') ) {
                    var principalinvestigators = store.get( 'PrincipalInvestigators' ).then(function(principalinvestigator) {
                        return principalinvestigator;
                    });
                }
                else {
                    var principalinvestigators = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var principalinvestigators = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( principalinvestigators );
                            return store.get( 'PrincipalInvestigators' );
                        });
                }
                return principalinvestigators;
            }



            /********************************************************************
            **
            **      PURCHASEORDER            **
            ********************************************************************/

            af.getPurchaseOrderById = function( key_id )
            {
                var urlSegment = 'getPurchaseOrderById&id=' + key_id;

                if( store.checkCollection( 'PurchaseOrder', key_id ) ) {
                    var purchaseorder = store.getById( 'PurchaseOrder', key_id )
                        .then(function(purchaseorder) {
                            return purchaseorder;
                        });
                }
                else {
                    var purchaseorder = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store purchaseorder in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return purchaseorder;
            }

            af.getAllPurchaseOrders = function( key_id )
            {
                var urlSegment = 'getAllPurchaseOrders';

                if( store.checkCollection('PurchaseOrders') ) {
                    var purchaseorders = store.get( 'PurchaseOrders' ).then(function(purchaseorder) {
                        return purchaseorder;
                    });
                }
                else {
                    var purchaseorders = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var purchaseorders = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( purchaseorders );
                            return store.get( 'PurchaseOrders' );
                        });
                }
                return purchaseorders;
            }



            /********************************************************************
            **
            **      SOLIDSCONTAINER            **
            ********************************************************************/

            af.getSolidsContainerById = function( key_id )
            {
                var urlSegment = 'getSolidsContainerById&id=' + key_id;

                if( store.checkCollection( 'SolidsContainer', key_id ) ) {
                    var solidscontainer = store.getById( 'SolidsContainer', key_id )
                        .then(function(solidscontainer) {
                            return solidscontainer;
                        });
                }
                else {
                    var solidscontainer = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store solidscontainer in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return solidscontainer;
            }

            af.getAllSolidsContainers = function( key_id )
            {
                var urlSegment = 'getAllSolidsContainers';

                if( store.checkCollection('SolidsContainers') ) {
                    var solidscontainers = store.get( 'SolidsContainers' ).then(function(solidscontainer) {
                        return solidscontainer;
                    });
                }
                else {
                    var solidscontainers = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var solidscontainers = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( solidscontainers );
                            return store.get( 'SolidsContainers' );
                        });
                }
                return solidscontainers;
            }



            /********************************************************************
            **
            **      WASTEBAG            **
            ********************************************************************/

            af.getWasteBagById = function( key_id )
            {
                var urlSegment = 'getWasteBagById&id=' + key_id;

                if( store.checkCollection( 'WasteBag', key_id ) ) {
                    var wastebag = store.getById( 'WasteBag', key_id )
                        .then(function(wastebag) {
                            return wastebag;
                        });
                }
                else {
                    var wastebag = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store wastebag in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return wastebag;
            }

            af.getAllWasteBags = function( key_id )
            {
                var urlSegment = 'getAllWasteBags';

                if( store.checkCollection('WasteBags') ) {
                    var wastebags = store.get( 'WasteBags' ).then(function(wastebag) {
                        return wastebag;
                    });
                }
                else {
                    var wastebags = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var wastebags = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( wastebags );
                            return store.get( 'WasteBags' );
                        });
                }
                return wastebags;
            }



            /********************************************************************
            **
            **      WASTETYPE            **
            ********************************************************************/

            af.getWasteTypeById = function( key_id )
            {
                var urlSegment = 'getWasteTypeById&id=' + key_id;

                if( store.checkCollection( 'WasteType', key_id ) ) {
                    var wastetype = store.getById( 'WasteType', key_id )
                        .then(function(wastetype) {
                            return wastetype;
                        });
                }
                else {
                    var wastetype = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store wastetype in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return wastetype;
            }

            af.getAllWasteTypes = function( key_id )
            {
                var urlSegment = 'getAllWasteTypes';

                if( store.checkCollection('WasteTypes') ) {
                    var wastetypes = store.get( 'WasteTypes' ).then(function(wastetype) {
                        return wastetype;
                    });
                }
                else {
                    var wastetypes = genericAPIFactory.read(urlSegment)
                        .then( function( returnedPromise) {
                            var wastetypes = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( wastetypes );
                            return store.get( 'WasteTypes' );
                        });
                }
                return wastetypes;
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
