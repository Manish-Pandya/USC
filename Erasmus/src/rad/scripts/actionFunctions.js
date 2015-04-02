'use strict';

angular
    .module('actionFunctionsModule',[])
    
        .factory('actionFunctionsFactory', function actionFunctionsFactory( modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods ){
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

            af.save = function( object, saveChildren )
            { 
                    if(!saveChildren)saveChildren = false;
                    //set a root scope marker as the promise so that we can use angular-busy directives in the view
                    return $rootScope[object.Class+'Saving'] = genericAPIFactory.save( object, false, saveChildren )
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
                return dataStore[flavor];
            }
            
            af.getViewMap = function(current)
            {
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
                        Name:'admin-pickups',
                        Label: 'Pickups',
                        Dashboard: true
                    },
                    {
                        Name:'radmin.pi-detail',
                        Label: 'Radiation Administration',
                        Dashboard: true
                    },
                    {
                        Name:'pi-rad-management',
                        Label: 'My Radiation Laboratory',
                        NoHead: true
                    },
                    {
                        Name:'solids',
                        Label: 'My Radiation Laboratory',
                    },
                    {
                        Name:'use-log',
                        Label: 'Use Log'
                    },
                    {
                        Name:'parcel-use-log',
                        Label: 'Package Use Log',
                        Dashboard: true
                    },
                    {
                        Name:'pickups',
                        Label: 'Pickups',
                        Dashboard: true
                    }
                ]

                var i = viewMap.length;
                while(i--){
                    if(current.name == viewMap[i].Name){
                        return viewMap[i];
                    }
                }
            }

            af.setSelectedView = function(view){
                $rootScope.selectedView = view;
            }

            /********************************************************************
            **
            **      MODALS
            **
            ********************************************************************/
            af.fireModal = function( templateName, object  )
            {
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
                            var hazards = store.get( 'Hazard' )
                                .then(
                                    function( hazards ){
                                        return hazards;
                                    }
                                );
                        }else{
                            var hazards = genericAPIFactory.read( urlSegment )
                                .then(
                                    function( returnedPromise ){
                                        var hazards = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                                        store.store( hazards );
                                        return store.get( 'Hazard' )
                                            .then(
                                                function( hazards ){
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
                    $rootScope.pi = pi;
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

            af.getAllCarboyUseCycles = function( key_id )
            {
                return dataSwitchFactory.getAllObjects('CarboyUseCycle');
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
                            if(tempPI.Authorizations.length){
                                store.store(tempPI.Authorizations);
                            }
                            pi.loadActiveParcels();
                            pi.loadAuthorizations();
                            pi.loadPurchaseOrders();
                            pi.loadCarboys();
                            pi.loadSolidsContainers();
                            return pi;
                        });
                }else{
                    pi.loadActiveParcels();
                    pi.loadPurchaseOrders();
                    pi.loadAuthorizations();
                    pi.loadCarboyUseCycles();
                    pi.loadSolidsContainers();
                    var defer = $q.defer();
                    defer.resolve(pi);
                    return defer.promise;
                }

            }

            af.getRadPIById = function(id)
            {   
                //no PI has been cached
                if(!store.checkCollection( 'PrincipalInvestigator' )){
                    var segment = "getRadPIById&id="+id+"&rooms=true";
                    return genericAPIFactory.read(segment)
                        .then( function( returnedPromise) {
                            var pi = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            if(pi.Rooms && pi.Rooms.length){
                                var rooms = modelInflatorFactory.instateAllObjectsFromJson( pi.Rooms );
                                store.store(rooms);
                                pi.Rooms = store.get('Room');  
                            }
                            if(pi.SolidsContainers && pi.SolidsContainers.length){
                                var containers = modelInflatorFactory.instateAllObjectsFromJson( pi.SolidsContainers );
                                store.store(containers);
                                pi.SolidsContainers = store.get('SolidsContainer');                             
                            }
                            if(pi.CarboyUseCycles && pi.CarboyUseCycles.length){
                                var cycles = modelInflatorFactory.instateAllObjectsFromJson( pi.CarboyUseCycles );
                                store.store(cycles);
                                pi.CarboyUseCycles = store.get('CarboyUseCycle');
                                var i = pi.CarboyUseCycles.length;
                                var carboys = [];
                                while(i--){
                                    pi.CarboyUseCycles[i].Carboy = modelInflatorFactory.instateAllObjectsFromJson(  pi.CarboyUseCycles[i].Carboy );
                                    carboys.push(pi.CarboyUseCycles[i].Carboy);
                                }
                                store.store(carboys);
                            }
                            if(pi.Authorizations && pi.Authorizations.length){
                                var auths = modelInflatorFactory.instateAllObjectsFromJson( pi.Authorizations );
                                store.store(auths);
                                pi.Authorizations = store.get('Authorization');
                            }
                            if(pi.ActiveParcels && pi.ActiveParcels.length){
                                var parcels = modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels );
                                store.store(parcels);
                                pi.ActiveParcels = store.get('Parcel');
                                var allUses = [];
                                var allAmounts = [];
                                var i = pi.ActiveParcels.length;
                                while(i--){
                                    if(pi.ActiveParcels[i].ParcelUses && pi.ActiveParcels[i].ParcelUses.length){
                                        pi.ActiveParcels[i].ParcelUses = modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels[i].ParcelUses );
                                        var j = pi.ActiveParcels[i].ParcelUses.length
                                        while(j--){                                            
                                            pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts = modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts );
                                            allUses = allUses.concat(pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts);
                                        }
                                        allUses = allUses.concat(pi.ActiveParcels[i].ParcelUses);
                                    }
                                    if(allUses.length)store.store(allUses);
                                    if(allAmounts.length)store.store(allAmounts);
                                }

                            }
                            if(pi.PurchaseOrders && pi.PurchaseOrders.length){
                                var orders = modelInflatorFactory.instateAllObjectsFromJson( pi.PurchaseOrders );
                                store.store(orders);
                                pi.PurchaseOrders = store.get('PurchaseOrder');                            
                            }
                            if(pi.Pickups && pi.Pickups.length){
                                var pickups = modelInflatorFactory.instateAllObjectsFromJson( pi.Pickups );
                                store.store(pickups);
                                pi.Pickups = store.get('Pickup');   
                            }   
                            console.log(pi);
                            store.store(pi);
                            return pi;
                        });

                    }
                    //PI has been cached
                    else{
                        var pi = store.getById('PrincipalInvestigator',id);
                        if(pi.Rooms && pi.Rooms.length){
                            var rooms = modelInflatorFactory.instateAllObjectsFromJson( pi.Rooms );
                            store.store(rooms);
                            pi.Rooms = store.get('Room');  
                        }
                        if(pi.SolidsContainers && pi.SolidsContainers.length){
                            var containers = modelInflatorFactory.instateAllObjectsFromJson( pi.SolidsContainers );
                            store.store(containers);
                            pi.SolidsContainers = store.get('SolidsContainer');                             
                        }
                        if(pi.CarboyUseCycles && pi.CarboyUseCycles.length){
                            var cycles = modelInflatorFactory.instateAllObjectsFromJson( pi.CarboyUseCycles );
                            store.store(cycles);
                            pi.CarboyUseCycles = store.get('CarboyUseCycle');
                            var i = pi.CarboyUseCycles.length;
                            var carboys = [];
                            while(i--){
                                pi.CarboyUseCycles[i].Carboy = modelInflatorFactory.instateAllObjectsFromJson(  pi.CarboyUseCycles[i].Carboy );
                                carboys.push(pi.CarboyUseCycles[i].Carboy);
                            }
                            store.store(carboys);
                        }
                        if(pi.Authorizations && pi.Authorizations.length){
                            var auths = modelInflatorFactory.instateAllObjectsFromJson( pi.Authorizations );
                            store.store(auths);
                            pi.Authorizations = store.get('Authorization');
                        }
                        if(pi.ActiveParcels && pi.ActiveParcels.length){
                            var parcels = modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels );
                            store.store(parcels);
                            pi.ActiveParcels = store.get('Parcel');
                            var allUses = [];
                            var allAmounts = [];
                            var i = pi.ActiveParcels.length;
                            while(i--){
                                if(pi.ActiveParcels[i].ParcelUses && pi.ActiveParcels[i].ParcelUses.length){
                                    pi.ActiveParcels[i].ParcelUses = modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels[i].ParcelUses );
                                    var j = pi.ActiveParcels[i].ParcelUses.length
                                    while(j--){                                            
                                        pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts = modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts );
                                        allUses = allUses.concat(pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts);
                                    }
                                    allUses = allUses.concat(pi.ActiveParcels[i].ParcelUses);
                                }
                                if(allUses.length)store.store(allUses);
                                if(allAmounts.length)store.store(allAmounts);
                            }

                        }
                        if(pi.PurchaseOrders && pi.PurchaseOrders.length){
                            var orders = modelInflatorFactory.instateAllObjectsFromJson( pi.PurchaseOrders );
                            store.store(orders);
                            pi.PurchaseOrders = store.get('PurchaseOrder');                            
                        }
                        if(pi.Pickups && pi.Pickups.length){
                            var pickups = modelInflatorFactory.instateAllObjectsFromJson( pi.Pickups );
                            store.store(pickups);
                            pi.Pickups = store.get('Pickup');   
                        }
                        console.log(pi)
                        //return a promise so return type is consistent
                        var defer = $q.defer();
                        defer.resolve(pi);
                        return defer.promise;
                    }

            }

            af.getParcelUses = function(parcel)
            {
                if(!store.checkCollection( 'ParcelUseAmounts' )){
                    var segment = "getParcelUsesByParcelId&id="+parcel.Key_id;
                    return genericAPIFactory.read(segment)
                        .then(
                            function(returnedUses){
                                //console.log(returnedUses.data);
                                var uses = modelInflatorFactory.instateAllObjectsFromJson( returnedUses.data );
                                store.store(uses);
                                console.log(uses);
                                var useAmounts = [];
                                var i = uses.length;
                                while(i--){
                                    var use = uses[i];
                                    var j = use.ParcelUseAmounts.length;
                                    while(j--)useAmounts = useAmounts.concat(use.ParcelUseAmounts);
                                }
                                var amounts = modelInflatorFactory.instateAllObjectsFromJson(useAmounts);
                                store.store(amounts);
                                parcel.loadUses();
                            }
                        )
                }else{
                    parcel.loadUses();
                    var defer = $q.defer();
                    defer.resolve(parcel);
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

            af.setInspection = function( PIKeyID, inspectorIds, inspectionId )
            {

                    //set inspectionId to empty strying if we are starting a new inspection
                    if(!inspectionId)inspectionId = '';

                    var url = 'initiateInspection&piId='+PIKeyID+'&'+$.param({inspectorIds:inspectorIds})+'&inspectionId='+inspectionId;
                    $rootScope.inspectionPromise = genericAPIFactory.read(url)
                        .then(
                            function( inspection ){
                                store.store( inspection );
                                return store.get( 'Inspection' )
                            },
                            function(promise){
                                
                            }
                        );  
                    return $rootScope.inspectionPromise;
            }

            af.resetInspectionRooms = function( roomIds, inspectionId )
            {

                    //we have changed the room collection for this inspection, so we set the new relationships on the server and get back and new collection of hazards  
                    var url = 'resetInspectionRooms&inspectionId='+inspectionId+'&'+$.param({roomIds:roomIds})+'&callback=JSON_CALLBACK';

                    $rootScope.inspectionPromise = genericAPIFactory.read( url )
                            .then(
                                function( inspection ){
                                    return inspection
                                },
                                function(promise){
                                } 
                            );  
                    return $rootScope.inspectionPromise;
                
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

                    $rootScope.inspectionPromise = genericAPIFactory.read( url )
                            .then(
                                function( HazardRoomRelations ){
                                    store.store( HazardRoomRelations, true );
                                    return HazardRoomRelations
                                },
                                function(promise){
                                } 
                            );  
                    return $rootScope.inspectionPromise;
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

            af.getRoomById = function(id) 
            {
                return dataSwitchFactory.getObjectById("Room", id);
            } 

            af.test = function(user)
            {
                    dataStoreManager.getById("User", user.Key_id).setName('updated');
                    //user.Supervisor.User.setName('updated');
            }

            /********************************************************************
            **
            **      SAVE CALLS
            **
            ********************************************************************/

            af.saveAuthorization = function( pi, copy, auth )
            {
                af.clearError();
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

            af.savePurchaseOrder = function( pi, copy, order )
            {
                af.clearError();
                console.log(copy.view_Start_date);
                copy.Start_date = convenienceMethods.setMysqlTime(af.getDate(copy.view_Start_date));
                copy.End_date = convenienceMethods.setMysqlTime(af.getDate(copy.view_End_date));

                return this.save( copy )
                    .then(
                        function(returnedPO){
                            returnedPO = modelInflatorFactory.instateAllObjectsFromJson( returnedPO );
                            if(order){
                                angular.extend(order, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedPO);
                                pi.PurchaseOrders.push(returnedPO);
                            }
                        },
                        af.setError('The Purchase Order could not be saved')
                    )
            }

            af.getDate = function(dateString){
                console.log(dateString)
                console.log(Date.parse(dateString))
                var seconds = Date.parse(dateString);
                console.log(seconds);
                //if( !dateString || isNaN(dateString) )return;
                var t = new Date(1970,0,1);
                t.setTime(seconds);
                console.log(t);
                return t;
            }

            af.getIsExpired = function(dateString){
                console.log(dateString)
                console.log(Date.parse(dateString))
                var seconds = Date.parse(dateString);
                console.log(new Date().getTime())
                console.log(seconds < new Date().getTime())
                return seconds < new Date().getTime();
            }

            af.saveParcel = function( pi, copy, parcel )
            {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedParcel){
                            returnedParcel = modelInflatorFactory.instateAllObjectsFromJson( returnedParcel );
                            if(parcel){
                                angular.extend(parcel, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedParcel);
                                pi.ActiveParcels.push(returnedParcel);
                            }
                        },
                        af.setError('The authorization could not be saved')
                    )
            }

            af.saveSolidsContainer = function( pi, copy, container )
            {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedContainer){
                            returnedContainer = modelInflatorFactory.instateAllObjectsFromJson( returnedContainer );
                            if(container){
                                angular.extend(container, copy)
                            }else{
                                returnedContainer.loadRoom();
                                dataStoreManager.addOnSave(returnedContainer);
                            }
                        },
                        af.setError('The Solids Container could not be saved')
                    )
            }

            af.saveCarboy = function( pi, copy, carboy )
            {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedCarboy){
                            returnedCarboy = modelInflatorFactory.instateAllObjectsFromJson( returnedCarboy );
                            if(carboy){
                                angular.extend(carboy, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedCarboy);
                                pi.SolidsContainers.push(returnedCarboy);
                            }
                        },
                        af.setError('The Solids Container could not be saved')
                    )
            }

            af.removeCarboyFromLab = function(cycle){
                af.clearError();
                cycle.Status = 'Decaying';
                return this.save( cycle )
                    .then(
                        function(returnedCycle){
                            returnedCycle = modelInflatorFactory.instateAllObjectsFromJson( returnedCycle );
                            angular.extend(cycle, returnedCycle)
                        },
                        af.setError('The Carboy could not be removed from the lab.')
                    )
            }

            af.addCarboyToLab = function(cycle, pi, room){
                cycle.Principal_investigator_id = pi.Key_id;
                cycle.Room_id = room.Key_id;
                cycle.Lab_date = convenienceMethods.setMysqlTime(new Date());
                cycle.Status = 'In Use';
                cycle.Is_active = true;

                af.clearError();
                return this.save( cycle )
                    .then(
                        function(returnedCycle){
                            returnedCycle = modelInflatorFactory.instateAllObjectsFromJson( returnedCycle );
                            angular.extend(cycle, returnedCycle);
                            pi.CarboyUseCycles.push(cycle);
                        },
                        af.setError('The Carboy could not be added to the lab.')
                    )
            }

            af.setError = function(errorString)
            {
                $rootScope.error = errorString + ' please check your internet connection and try again';
            }

            af.clearError = function()
            {
                $rootScope.error = null;
            }

            af.addWasteBagToSolidsContainer = function(container)
            {
                var bag = {
                    Date_added: convenienceMethods.setMysqlTime(new Date()),
                    Is_active: true,
                    Class: "WasteBag",
                    Container_id: container.Key_id
                };

                af.clearError();
                return this.save( bag )
                    .then(
                        function(returnedBag){
                            returnedBag = modelInflatorFactory.instateAllObjectsFromJson( returnedBag );
                            console.log(returnedBag);
                            angular.extend(bag, returnedBag);
                            container.CurrentWasteBags.push(returnedBag);
                        },
                        af.setError('The Waste Bage could not be added to the Receptical.')
                    )
            }

            af.removeWasteBagFromContainer = function(container, bag){
                console.log(convenienceMethods.setMysqlTime(new Date()))
                bag.Date_removed = convenienceMethods.setMysqlTime(new Date());
                console.log(bag);
                af.clearError();
                return this.save( bag )
                    .then(
                        function(returnedBag){
                            console.log(returnedBag);
                            returnedBag = modelInflatorFactory.instateAllObjectsFromJson( returnedBag );
                            angular.extend(bag, returnedBag)
                        },
                        af.setError('The Carboy could not be removed from the lab.')
                    )
            }

            af.saveParcelUse = function(parcel, copy, use){
                af.clearError();
                copy.Date_used = convenienceMethods.setMysqlTime(af.getDate(copy.view_Date_used));
                return this.save( copy )
                    .then(
                        function(returnedUse){
                            console.log(returnedUse);
                            returnedUse = modelInflatorFactory.instateAllObjectsFromJson( returnedUse );
                            var i = returnedUse.ParcelUseAmounts.length;
                            while(i--){
                                returnedUse.ParcelUseAmounts[i] = modelInflatorFactory.instateAllObjectsFromJson( returnedUse.ParcelUseAmounts[i] );
                                if(returnedUse.ParcelUseAmounts[i].Carboy){
                                    returnedUse.ParcelUseAmounts[i].Carboy = null;
                                    returnedUse.ParcelUseAmounts[i].loadCarboy();
                                }
                            }
                            if(use){
                                angular.extend(use, returnedUse)
                            }else{
                                dataStoreManager.addOnSave(returnedUse);
                                parcel.ParcelUses.push(returnedUse)
                            }
                            $rootScope.ParcelUseCopy = {};
                            use.edit = false;
                            af.clearError();
                            return parcel;
                        },
                        function(){
                            af.setError('The usage could not be saved.')
                        }
                    )
            }

            af.savePickup = function(originalPickup, editedPickup){
                af.clearError();
                if(editedPickup.Status == "PICKED UP" || editedPickup.Status == "AT RSO" && !editedPickup.Pickup_date)editedPickup.Pickup_date = convenienceMethods.setMysqlTime(new Date());
                return this.save( editedPickup, true )
                    .then(
                        function(returnedPickup){
                            returnedPickup = modelInflatorFactory.instateAllObjectsFromJson( returnedPickup );
                            
                            //the pickup is new, so it has no key id
                            if(!originalPickup.Key_id){
                                dataStoreManager.addOnSave(returnedPickup);
                                pi.Pickups.push(returnedPickup);
                                pi.CarboyUseCycles = null;
                                pi.Parcels = null;
                            }
                            //the pickup had a key id, so we are mutating a pickup that already existed
                            else{
                                angular.extend(originalPickup, returnedPickup);
                            }
                        },
                        af.setError('The pickup could not be saved')
                    )
            }

            af.u

        	return af;
		});
