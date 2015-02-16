'use strict';

angular
    .module('actionFunctionsModule',[])
    
        .factory('actionFunctionsFactory', function actionFunctionsFactory( modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal ){
        	var af = {};
            var store = dataStoreManager;
            //give us access to this factory in all views.  Because that's cool.
            $rootScope.af = this;

            store.$q = $q;


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
                                return false;
                            }
                        );

            }

            af.save = function( object )
            {
                    //set a root scope marker as the promise so that we can use angular-busy directives in the view
                    return $rootScope[object.Class+'Saving'] = genericAPIFactory.save( object )
                        .then(
                            function( returnedData ){
                                return returnedData.data;
                            },
                            function( error )
                            {
                                //object.Name = error;
                               // object.setIs_active( !object.Is_active );
                                $rootScope.error = 'error';
                            }
                        );
            }

            af.getById = function( objectFlavor, key_id )
            {
                return store.getById(objectFlavor, key_id );       
            }

            af.getAll = function(className) {
                return dataSwitchFactory.getAllObjects(className);
            }

            af.getCachedCollection = function(flavor)
            {
                console.log(flavor);
                console.log(dataStore);
                return dataStore[flavor];
            }
            
            af.getViewMap = function(current)
            {
                //console.log(current);
                var viewMap = [
                    {
                        Name: 'rad-home',
                        Label: 'Radiation Center',
                        Dashboard:false
                    },
                    {
                        Name:'radmin',
                        Label: 'Radiation Administration',
                        Dashboard: true
                    },
                    {
                        Name:'radmin.pi-detail',
                        Label: 'Radiation Administration',
                        Dashboard: true
                    }
                ]

                var i = viewMap.length;
                while(i--){
                    //console.log(current.name);
                    if(current.name == viewMap[i].Name){
                        return viewMap[i];
                    }
                }
            }

            /********************************************************************
            **
            **      MODALS
            **
            ********************************************************************/
            af.fireModal = function( templateName, object  )
            {
                console.log(templateName);
                if(object)af.setModalData(object);
                var modalInstance = $modal.open({
                  templateUrl: templateName+'.html',
                  controller: 'GenericModalCtrl'
                });
            }

            af.setModalData = function(thing)
            {
                dataStoreManager.setModalData(thing);
            }

            af.getModalData = function()
            {
                return dataStoreManager.getModalData();
            }

            af.deleteModalData = function()
            {
                dataStore.modalData = [];
            }

            af.createCopy = function(obj)
            {
                $rootScope[obj.Class+'Copy'] = dataStoreManager.createCopy(obj);
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
                return dataSwitchFactory.getAllObjects('User');
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
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'User' ) ) );
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'PrincipalInvestigator' ) ) );
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'PrincipalInvestigatorRoomRelation' ) ) );
                                    store.store( modelInflatorFactory.instateAllObjectsFromJson( store.get( 'Room' ) ) );

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
                return dataSwitchFactory.getAllObjects('Hazard');
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
                        if( store.checkCollection( 'Hazard', nodeId ) ){
                            console.log('check positive');
                            var hazards = store.get( 'Hazard' )
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
                                        return store.get( 'Hazard' )
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

            af.getPrincipalInvestigatorById = function( key_id )
            {
                var urlSegment = 'getPIById&id=' + key_id;

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

            af.getAllPIs= function()
            {
                return dataSwitchFactory.getAllObjects('PrincipalInvestigator');
            }

            af.getAllPIRoomRelations = function()
            {
                    var urlSegment = 'getAllPrincipalInvestigatorRoomRelations';

                    if( store.checkCollection( 'PrincipalInvestigatorRoomRelation' ) ){
                            var relations = $q.defer()
                            var storedRelations = store.get( 'PrincipalInvestigatorRoomRelation' );
                            relations.resolve(storedRelations);
                            return relations.promise
                    }else{
                            var relations = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        var returnedRelations = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( returnedRelations );
                                        return store.get( 'PrincipalInvestigatorRoomRelation' );
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
                return dataSwitchFactory.getAllObjects('Isotope');
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

            af.getAllAuthorizations = function( )
            {
                return dataSwitchFactory.getAllObjects('Authorization');
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
                return dataSwitchFactory.getAllObjects('Carboy');
            }

            af.saveAuthorization = function( pi, copy, auth )
            {
                af.clearError();
                console.log(auth);
                return this.save( copy )
                    .then(
                        function(returnedAuth){
                            returnedAuth = modelInflatorFactory.instateAllObjectsFromJson( returnedAuth );
                            if(auth){
                                angular.extend(auth, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedAuth);
                                pi.Authorizations.push(returnedAuth);
                            }
                        },
                        af.setError('The authorization could not be saved')
                    )
            }

            af.setError = function(errorString)
            {
                $rootScope.error = errorString + ' please check your internet connection and try again';
            }

            af.clearError = function()
            {
                $rootScope.error = '';
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
                return dataSwitchFactory.getAllObjects('Drum');
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
                return dataSwitchFactory.getAllObjects('Parcel');
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
                return dataSwitchFactory.getAllObjects('ParcelUse');
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
                return dataSwitchFactory.getAllObjects('ParcelUseAmount');
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
                return dataSwitchFactory.getAllObjects('Pickup');
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

            af.getAllPurchaseOrders = function(  )
            {
                return dataSwitchFactory.getAllObjects('PurchaseOrder');
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
                return dataSwitchFactory.getAllObjects('SolidsContainer');
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
                return dataSwitchFactory.getAllObjects('WasteBag');
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
                return dataSwitchFactory.getAllObjects('WasteType');
            }

            /********************************************************************
            **
            **      RAD PI
            **
            ********************************************************************/

            af.getRadPI = function(pi)
            {
                
                if(!store.checkCollection( 'Authorization')){
                    var segment = "getRadPIById&id="+pi.Key_id;
                    return genericAPIFactory.read(segment)
                        .then( function( returnedPromise) {
                            var tempPI = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            console.log(tempPI);
                            if(tempPI.Authorizations.length){
                                console.log(tempPI.loadAuthorizations);
                                store.store(tempPI.Authorizations);
                            }
                            if(tempPI.ActiveParcels.length)store.store(tempPI.ActiveParcels);
                            store.store(tempPI.User);
                            pi.loadActiveParcels();
                            pi.loadAuthorizations();
                            return pi;
                        });
                }else{
                    pi.loadActiveParcels();
                    pi.loadAuthorizations();
                    var defer = $q.defer();
                    defer.resolve(pi);
                    return defer.promise;
                }

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
                return dataSwitchFactory.getAllObjects('Room');
            }

            af.test = function(user)
            {
                    dataStoreManager.getById("User", user.Key_id).setName('updated');
                    //user.Supervisor.User.setName('updated');
                    console.log(dataStoreManager.getById("PrincipalInvestigator", user.Supervisor_id));            
            }

        	return af;
		});
