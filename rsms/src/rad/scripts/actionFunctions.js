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

            af.createCopy = function(obj)
            {
                obj.edit = true;
                $rootScope[obj.Class+'Copy'] = dataStoreManager.createCopy(obj);
            }

            af.cancelEdit = function( obj )
            {
                    obj.edit = false;
                    $rootScope[obj.Class+'Copy'] = {};
                    //store.replaceWithCopy( object );
            }

            af.setObjectActiveState = function( object )
            {

                    object.setIs_active( !object.Is_active );

                    //set a root scope marker as the promise so that we can use angular-busy directives in the view
                    $rootScope[object.Class+'Saving'] = genericAPIFactory.save( object )
                        .then(
                            function( returnedPromise ){
                                if(typeof returnedPromise === 'object')angular.extend(object, returnedPromise);
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
                        Name:'radmin.wipe-tests',
                        Label: 'Radiation Administration -- Wipe Tests',
                        Dashboard: true
                    },
                    {
                        Name:'radmin.inventories',
                        Label: 'Radiation Administration -- Quarterly Inventories',
                        Dashboard: true
                    },
                    {
                        Name:'radmin-quarterly-inventory',
                        Label: 'Radiation Administration -- Quarterly Inventories',
                        Dashboard: true
                    },
                    {
                        Name:'radmin.carboys',
                        Label: 'Radiation Administration -- Carboys',
                        Dashboard: true
                    },
                    {
                        Name:'radmin.orders',
                        Label: 'Radiation Administration -- Packages',
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
                        Dashboard:true
                    },
                    {
                        Name:'pi-orders',
                        Label: 'My Radiation Laboratory',
                        Dashboard:true
                    },
                    {
                        Name:'pi-wipes',
                        Label: 'My Radiation Laboratory -- Wipe Tests',
                        Dashboard:true
                    },
                    {
                        Name:'use-log',
                        Label: 'Use Log',
                        Dashboard:true
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
                    },
                    {
                        Name:'inspection-wipes:inspection',
                        Label: 'Inspection Wipes'
                    },
                    {
                        Name:'radmin.disposals',
                        Label: 'Disposals',
                        Dashboard: true
                    },
                    {
                        Name: 'current-inventories',
                        Label: 'Current Inventories',
                        Dashboard: true
                    },
                    {
                        Name:'quarterly-inventory',
                        Label: 'Quarterly Inventory',
                        Dashboard: true
                    },
                    {
                        Name:'radmin.isotopes',
                        Label: 'Radiation Administration -- Isotopes',
                        Dashboard: true
                    },
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
                return this.getAllUsers()
                    .then(
                        function(){
                            return dataSwitchFactory.getAllObjects('PrincipalInvestigator');
                        }
                    )

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

            af.saveIsotope = function(copy, isotope)
            {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedIsotope){
                            returnedIsotope = modelInflatorFactory.instateAllObjectsFromJson( returnedIsotope );
                            if(isotope){
                                angular.extend(isotope, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedIsotope);
                            }
                        },
                        af.setError('The Isotope could not be saved')
                    )
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
                return dataSwitchFactory.getAllObjects('CarboyUseCycle', true);
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

            af.replaceDrum = function(drum){
                var segment = "getDrumById&id="+drum.Key_id;
                return genericAPIFactory.read(segment)
                        .then(
                            function(returnedDrum){
                                angular.extend(drum, returnedDrum.data);
                            }
                        )
            }



            /********************************************************************
            **
            **      PARCEL            **
            ********************************************************************/

            af.getParcelById = function( key_id )
            {
                return dataSwitchFactory.getObjectById("Parcel",key_id, true);
            }

            af.getAllParcels = function( key_id )
            {
                return dataSwitchFactory.getAllObjects('Parcel',true);
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
                return dataSwitchFactory.getAllObjects('Pickup', true);
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
                    var segment = "getRadPIById&id="+pi.Key_id+"&rooms=true";
                    return genericAPIFactory.read(segment)
                        .then( function( returnedPromise) {
                            var tempPI = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data, null, true );
                            //pi.loadRooms();
                            for(var prop in tempPI){
                                store.store(tempPI[prop]);
                                if(prop == "Pi_authorization"){
                                    store.store(tempPI[prop].Authorizations);
                                }
                            }

                            pi.Rooms = tempPI.Rooms;
                            pi.Departments = tempPI.Departments;
                            pi.loadPIAuthorizations();
                            pi.loadActiveParcels();
                            pi.loadPurchaseOrders();
                            pi.loadCarboyUseCycles();
                            pi.loadSolidsContainers();
                            return pi;
                        });
            }

            af.getRadPIById = function(id)
            {
                if(dataStoreManager.getById("PrincipalInvestigator", id)){
                    var defer = $q.defer();
                    defer.resolve(dataStoreManager.getById("PrincipalInvestigator", id));
                    return defer.promise;
                }
                var segment = "getRadPIById&id="+id+"&rooms=true";
                return genericAPIFactory.read(segment)
                    .then( function( returned ) {
                        var pi = returned.data;
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.User ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.Pi_authorization ));
                        if(pi.Pi_authorization.Authorizations){
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.Pi_authorization.Authorizations ));
                        }
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.ActiveParcels ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.ScintVialCollections ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.PurchaseOrders ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.CarboyUseCycles ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.CurrentScintVialCollections ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson( pi.Quarterly_inventories ));
                        store.store(modelInflatorFactory.instateAllObjectsFromJson(pi.SolidsContainers));
                        console.log(pi);
                        
                        store.store(modelInflatorFactory.instateAllObjectsFromJson(pi.Pickups));

                        var i = pi.ActiveParcels.length;
                        while (i--) {
                            if (pi.ActiveParcels[i].ParcelUses && pi.ActiveParcels[i].ParcelUses.length) {
                                store.store(modelInflatorFactory.instateAllObjectsFromJson(pi.ActiveParcels[i].ParcelUses.length));
                                var j = pi.ActiveParcels[i].ParcelUses.length;
                                while (j--) {

                                    if (pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts) {
                                        var k = pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts.length;
                                        while (k--) {
                                            store.store(modelInflatorFactory.instateAllObjectsFromJson(pi.ActiveParcels[i].ParcelUses[j].ParcelUseAmounts[k]));
                                        }
                                    }
                                }
                            }
                        }



                        store.store(modelInflatorFactory.instateAllObjectsFromJson(pi));
                       
                        pi = dataStoreManager.getById("PrincipalInvestigator", id);
                        if (pi) {
                            pi.loadSolidsContainers();
                            pi.loadPickups();
                            pi.loadActiveParcels();
                            pi.loadRooms();
                            pi.loadPurchaseOrders();
                            pi.loadCarboyUseCycles();
                            pi.loadPickups();
                            pi.loadPIAuthorizations();
                            pi.loadUser();
                            pi.loadWasteBags();
                            pi.loadCurrentScintVialCollections();
                            var i = pi.Pickups.length;
                            while (i--) {
                                pi.Pickups[i].loadCurrentScintVialCollections();
                                pi.Pickups[i].loadCarboyUseCycles();
                            }

                            var i = pi.SolidsContainers.length;
                            while(i--){
                                pi.SolidsContainers[i].loadCurrentWasteBags();
                                pi.SolidsContainers[i].loadWasteBagsForPickup();
                            }
                           
                        }
                        return pi;
                    });
            }

            af.getParcelUses = function(parcel)
            {
                if(!store.checkCollection( 'ParcelUseAmounts' )){
                    var segment = "getParcelUsesByParcelId&id="+parcel.Key_id;
                    return genericAPIFactory.read(segment)
                        .then(
                            function(returnedUses){
                                var uses = modelInflatorFactory.instateAllObjectsFromJson( returnedUses.data );
                                store.store(uses);
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
                                pi.Pi_authorization.Authorizations.push(returnedAuth);
                            }
                        },
                        af.setError('The authorization could not be saved')
                    )
            }

            af.savePurchaseOrder = function( pi, copy, order )
            {
                af.clearError();
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
                var seconds = Date.parse(dateString);
                //if( !dateString || isNaN(dateString) )return;
                var t = new Date(1970,0,1);
                t.setTime(seconds);
                return t;
            }

            af.getIsExpired = function(dateString){
                var seconds = Date.parse(dateString);
                return seconds < new Date().getTime();
            }

            af.saveParcel = function( copy, parcel, pi )
            {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedParcel){
                            returnedParcel = modelInflatorFactory.instateAllObjectsFromJson( returnedParcel );
                            if(copy.Key_id){
                                angular.extend(parcel, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedParcel);
                                pi.ActiveParcels.push(returnedParcel);
                            }
                        }
                    )
            }

            af.saveParcelWipesAndChildren = function( copy, parcel )
            {
                af.clearError();
                copy.Status = Constants.PARCEL.STATUS.WIPE_TESTED;
                 return $rootScope.SavingParcelWipe = genericAPIFactory.save( copy, 'saveParcelWipesAndChildren' )
                    .then(
                        function(returnedParcel){
                            returnedParcel = modelInflatorFactory.instateAllObjectsFromJson( returnedParcel );
                            if(parcel){
                                angular.extend(parcel, copy, true);
                                parcel.edit = false;
                                parcel.Wipe_test[0].edit = false;
                                var i = parcel.Wipe_test[0].Parcel_wipes.length;
                                while(i--){
                                    parcel.Wipe_test[0].Parcel_wipes[i].edit = false;
                                    angular.extend(parcel.Wipe_test[0].Parcel_wipes[i], copy.Wipe_test[0].Parcel_wipes[i]);
                                    if(!parcel.Wipe_test[0].Parcel_wipes[i].Location)parcel.Wipe_test[0].Parcel_wipes.splice(i,1);
                                }
                            }
                        },
                        af.setError('The package could not be saved')
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
                                angular.extend(container, copy);
                            }else{
                                returnedContainer.loadRoom();
                                dataStoreManager.addOnSave(returnedContainer);
                                pi.SolidsContainers.push(returnedContainer);
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
                                dataStoreManager.store(returnedCarboy);
                            }
                        },
                        af.setError('The Solids Container could not be saved')
                    )
            }

            af.saveCarboyUseCycle = function( copy, cycle, poured )
            {
                af.clearError();
                if(poured){
                    copy.Pour_date = convenienceMethods.setMysqlTime(new Date());
                    copy.Status = Constants.CARBOY_USE_CYCLE.STATUS.AVAILABLE;
                }
                return this.save( copy )
                    .then(
                        function(returnedCycle){
                            returnedCycle = modelInflatorFactory.instateAllObjectsFromJson( returnedCycle );
                            if(cycle){
                                var i = returnedCycle.Carboy_reading_amounts.length;
                                while(i--){
                                    dataStoreManager.getById("CarboyReadingAmount", returnedCycle.Carboy_reading_amounts[i].Key_id).Pour_allowed_date = returnedCycle.Carboy_reading_amounts[i].Pour_allowed_date;
                                }
                                cycle.Volume = returnedCycle.Volume;
                                cycle.Pour_allowed_date = returnedCycle.Pour_allowed_date;

                                cycle.edit = false;
                            }else{
                                dataStoreManager.addOnSave(returnedCycle);
                            }
                        },
                        af.setError('The Carboy could not be saved')
                    )
            }

            af.removeCarboyFromLab = function(cycle){
                af.clearError();
                cycle.Status = Constants.CARBOY_USE_CYCLE.STATUS.DECAYING;
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
                cycle.Lab_date = convenienceMethods.setMysqlTime(new Date());
                cycle.Status = Constants.CARBOY_USE_CYCLE.STATUS.IN_USE;
                cycle.Is_active = true;
                console.log(cycle);
                af.clearError();
                return this.save( cycle )
                    .then(
                        function (returnedCycle) {
                            var storedCycle = dataStoreManager.getById('CarboyUseCycle', returnedCycle.Key_id);
                            angular.extend(storedCycle, returnedCycle);
                            //returnedCycle.Carboy = cycle.Carboy;
                            storedCycle.loadRoom();
                            storedCycle.loadCarboy();
                            pi.CarboyUseCycles.push(storedCycle);
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

            af.changeWasteBag = function (container, bag)
            {
                af.clearError();
                return this.save( bag, false, "changeWasteBag" )
                    .then(
                        function(returnedBag){
                            returnedBag = modelInflatorFactory.instateAllObjectsFromJson( returnedBag );
                            container.CurrentWasteBags.push(returnedBag);
                            bag.Date_removed = returnedBag.Date_added;
                        },
                        af.setError('The Waste Bage could not be added to the Receptical.')
                    )
            }

            af.addWasteBagToSolidsContainer = function (container) {
                var bag = {
                    Date_added: convenienceMethods.setMysqlTime(new Date()),
                    Is_active: true,
                    Class: "WasteBag",
                    Container_id: container.Key_id
                };

                af.clearError();
                return this.save(bag)
                    .then(
                        function (returnedBag) {
                            returnedBag = modelInflatorFactory.instateAllObjectsFromJson(returnedBag);
                            console.log(returnedBag);
                            angular.extend(bag, returnedBag);
                            container.CurrentWasteBags.push(returnedBag);
                        },
                        af.setError('The Waste Bage could not be added to the Receptical.')
                    )
            }

            af.removeWasteBagFromContainer = function(container, bag){
                bag.Date_removed = convenienceMethods.setMysqlTime(new Date());
                af.clearError();
                return this.save( bag )
                    .then(
                        function(returnedBag){
                            returnedBag = modelInflatorFactory.instateAllObjectsFromJson(returnedBag);
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
                            returnedUse = modelInflatorFactory.instateAllObjectsFromJson(returnedUse);
                            console.log(returnedUse);
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
                            
                            parcel.Remainder = returnedUse.ParcelRemainder;
                            parcel.AmountOnHand = returnedUse.ParcelAmountOnHand;

                            use.edit = false;
                            af.clearError();
                            return parcel;
                        },
                        function () {
                            if (use) {
                                var i = use.ParcelUseAmounts.length;
                                while (i--) {
                                    use.ParcelUseAmounts[i].OldQuantity = use.ParcelUseAmounts[i].Curie_level;
                                }
                            }
                            
                            af.setError('The usage could not be saved.')
                        }
                    )
            }

            af.savePickup = function(originalPickup, editedPickup, saveChildren){
                af.clearError();

                //We can tell the server to save the child objects of this pickup, setting their pickup IDs and pickup date properties, if applicable.
                if(!saveChildren)saveChildren = false;

                //if this Pickup has been picked up by RSO, set it's pickup date.  If it is back at the radiation safety office, but hasn't been marked as picked up, also set the pickup date.
                if(editedPickup.Status == Constants.PICKUP.STATUS.PICKED_UP || editedPickup.Status == Constants.PICKUP.STATUS.AT_RSO && !editedPickup.Pickup_date)editedPickup.Pickup_date = convenienceMethods.setMysqlTime(new Date());
                return this.save(editedPickup, saveChildren)
                    .then(
                        function (returnedPickup) {
                            console.log(returnedPickup);

                            returnedPickup = modelInflatorFactory.instateAllObjectsFromJson(returnedPickup);
                            var pi = dataStoreManager.getById("PrincipalInvestigator", returnedPickup.Principal_investigator_id);
                            console.log(returnedPickup);
                            if (saveChildren) {
                                //set pickup ids for items that are included in pickup
                                var i = returnedPickup.Waste_bags.length;
                                while(i--){
                                        
                                        //find the cached WasteBag with the same key_id as the one from the server, and update its properties
                                        //remove this WasteBag from it's containers collection of WasteBags ready to be have a pickup requested.
                                        var container = dataStoreManager.getById('SolidsContainer', returnedPickup.Waste_bags[i].Container_id);
                                        console.log(container);
                                        var j = container.WasteBagsForPickup.length;
                                        while (j--) {
                                            if (container.WasteBagsForPickup[j].Key_id == returnedPickup.Waste_bags[i].Key_id) {
                                                angular.extend(container.WasteBagsForPickup[j], returnedPickup.Waste_bags[i]);
                                            }
                                        }
                                        //if we've added the container's current waste bag, add set its pickup id
                                        if (container.includeCurrentBag) {
                                            var bag = dataStoreManager.getById("WasteBag", container.CurrentWasteBags[0].Key_id);
                                            bag.Pickup_id = returnedPickup.Key_id;
                                            container.CurrentWasteBags[0].Pickup_id = returnedPickup.Key_id;
                                        }
                                }
                              

                                var i = returnedPickup.Carboy_use_cycles.length;
                                while(i--){
                                    if(dataStoreManager.getById('CarboyUseCycle', returnedPickup.Carboy_use_cycles[i].Key_id)){
                                        //find the cached CarboyUseCycle with the same key_id as the one from the server, and update its properties
                                        angular.extend(dataStoreManager.getById('CarboyUseCycle', returnedPickup.Carboy_use_cycles[i].Key_id),returnedPickup.Carboy_use_cycles[i]);
                                    }
                                }

                                var i = returnedPickup.Scint_vial_collections.length;
                                while (i--) {
                                    if(dataStoreManager.getById('ScintVialCollection', returnedPickup.Scint_vial_collections[i].Key_id)){
                                        //find the cached ScintVialCollection with the same key_id as the one from the server, and update its properties
                                        angular.extend(dataStoreManager.getById('ScintVialCollection', returnedPickup.Scint_vial_collections[i].Key_id),returnedPickup.Scint_vial_collections[i]);
                                    }
                                }
                            }
                            returnedPickup.loadCurrentScintVialCollections();
                            returnedPickup.loadWasteBags();
                            returnedPickup.loadCarboyUseCycles();
                             //the pickup is new, so add it to the cache and the PI's collection of pickups
                            if (!originalPickup.Key_id) {
                                dataStoreManager.store(returnedPickup);
                                if (!pi.Pickups) pi.Pickups = [];
                                pi.Pickups.push(returnedPickup);
                            }
                            //the pickup had a key id, so we are mutating a pickup that already existed.
                            else{
                                originalPickup.Requested_date = returnedPickup.Requested_date;
                                originalPickup.Pickup_date = returnedPickup.Pickup_date;
                                originalPickup.Status = returnedPickup.Status;
                            }
                        },
                        af.setError('The pickup could not be saved')
                    )
            }

            af.removeFromPickup = function(object, pickupCollection, pi, admin){
                var copy = dataStoreManager.createCopy(object);
                copy.Pickup_id = null;

                //Set labels for each kind of child the pickup might have, so we can display a human readable error if the save fails.
                if(copy.Class == "CarboyUseCycle"){
                    copy.Status = Constants.CARBOY_USE_CYCLE.STATUS.IN_USE;
                    var label = "Carboy";
                }else if(copy.Class == "WasteBag"){
                    var label = "Wate Bag";
                }else{
                    var label = "Scintillation Vials";
                }

                var pickup = dataStoreManager.getById("Pickup", object.Pickup_id);
                console.log(dataStore);
                //return;
              
                return this.save( copy )
                    .then(
                        function(returnedObj){
                            angular.extend(object, returnedObj);
                            if(copy.Class == "WasteBag" && !admin){
                                var container = dataStoreManager.getById("SolidsContainer", copy.Container_id);
                                if (container.CurrentWasteBags && container.CurrentWasteBags.length) {
                                    if (copy.Key_id == container.CurrentWasteBags[0].Key_id) {
                                        container.includeCurrentBag = false;
                                    }
                                } else {
                                    container.loadCurrentWasteBags();
                                }
                                
                            }
                            var i = pickupCollection.length;
                            while(i--){
                                if(object.Key_id == pickupCollection[i].Key_id)pickupCollection.splice(i,1);
                            }
                            console.log(object);
                            object.Pickup_id = null;
                            //Set labels for each kind of child the pickup might have, so we can display a human readable error if the save fails.
                            if (copy.Class == "CarboyUseCycle") {
                            } else if (copy.Class == "WasteBag") {
                            } else {
                                pi.CurrentScintVialCollections[0].include = false;
                            }
                            return pickup;
                        },
                        af.setError('The ' + label + ' could not removed from the pickup.')
                    ).then(
                        function (pickup) {
                            //if the pickup is now empty, delete it
                            if (!pickup.Carboy_use_cycles.length
                                && !pickup.Scint_vial_collections.length
                                && !pickup.Waste_bags.length) {
                                var urlSegment = "deletePickupById&id=" + pickup.Key_id;
                                return genericAPIFactory.read(urlSegment)
                                        .then(
                                            function (returned) {
                                                var pi = dataStoreManager.getById("PrincipalInvestigator", pickup.Principal_investigator_id);
                                                var i = pi.Pickups.length;
                                                while (i--) {
                                                    if (pi.Pickups[i].Key_id == pickup.Key_id) {
                                                        pi.Pickups.splice(i, 1);
                                                    }
                                                }

                                                //this will remove the pikcup from the dataStore, but the reference to it in the PIs collection will persist, freed
                                                delete dataStore.Pickup[dataStore.PickupMap[pickup.Key_id]];
                                                //also remove it from the pi
                                            }
                                        )
                                 }
                            }
                    )
            }


            af.adminRemoveFromPickup = function(object){
                var copy = dataStoreManager.createCopy(object);
                //Set labels for each kind of child the pickup might have, so we can display a human readable error if the save fails.
                if(copy.Class == "CarboyUseCycle"){
                    copy.Status = Constants.CARBOY_USE_CYCLE.STATUS.IN_USE;
                    var label = "Carboy";
                }else if(copy.Class == "WasteBag"){
                    var label = "Wate Bag";
                }else{
                    var label = "Scintillation Vials";
                }
                copy.Pickup_id = null;
                return this.save( copy )
                    .then(
                        function(returnedObj){
                            angular.extend(object, returnedObj);
                            object.removed = true;
                        },
                        af.setError('The ' + label + ' could not removed from the pickup.')
                    )
            }

            af.adminAddToPickup = function(object, pickup){
                var copy = dataStoreManager.createCopy(object);
                //Set labels for each kind of child the pickup might have, so we can display a human readable error if the save fails.
                if(copy.Class == "CarboyUseCycle"){
                    copy.Status = Constants.CARBOY_USE_CYCLE.STATUS.IN_USE;
                    var label = "Carboy";
                }else if(copy.Class == "WasteBag"){
                    var label = "Wate Bag";
                }else{
                    var label = "Scintillation Vials";
                }
                copy.Pickup_id = pickup.Key_id;
                return this.save( copy )
                    .then(
                        function(returnedObj){
                            angular.extend(object, returnedObj);
                            object.removed = false;
                        },
                        af.setError('The ' + label + ' could not added to the pickup.')
                    )
            }

            /****************************************************************************************
            **
            **          WIPE TESTS
            **
            ****************************************************************************************/

            /*  Parcel Wipe Tests */
            af.getAllParcelWipeTests = function(){
                return dataSwitchFactory.getAllObjects('ParcelWipeTest');
            }

            af.getAllParcelWipeTests = function(){
                return dataSwitchFactory.getAllObjects('ParcelWipe');
            }

            af.getAllInspectionWipeTests = function(){
                return dataSwitchFactory.getAllObjects('InspectionWipeTest');
            }

            af.getAllInspectionWipes = function(){
                return dataSwitchFactory.getAllObjects('InspectionWipe');
            }

            af.getAllParcelWipeTests = function(){
                return dataSwitchFactory.getAllObjects('ParcelWipe');
            }

            af.getAllParcelWipeTests = function(){
                return dataSwitchFactory.getAllObjects('ParcelWipe');
            }

            af.saveParcelWipeTest = function(parcel) {
                af.clearError();
                var copy = $rootScope.ParcelWipeTestCopy;
                return this.save( copy )
                    .then(
                        function(returnedPWT){
                            if(parcel.Wipe_test.length){
                                returnedPWT = modelInflatorFactory.instateAllObjectsFromJson( returnedPWT );
                                angular.extend(parcel.Wipe_test[0], copy)
                            }else{
                                returnedPWT.Parcel_wipes = [];
                                //by default, ParcelWipeTests have a collection of 6 ParcelWipes, hence the magic number
                                var i = 6
                                while(i--){
                                    var parcelWipe = new window.ParcelWipe();
                                    parcelWipe.Parcel_wipe_test_id = returnedPWT.Key_id;
                                    parcelWipe.Class = "ParcelWipe";
                                    parcelWipe.edit = true;
                                    returnedPWT.Parcel_wipes.push(parcelWipe);
                                }

                                returnedPWT = modelInflatorFactory.instateAllObjectsFromJson( returnedPWT );
                                returnedPWT.adding = true;
                                returnedPWT.Parcel_wipes[0].Location = "Background";
                                dataStoreManager.store(returnedPWT);
                                parcel.Wipe_test.push(returnedPWT);
                            }
                            parcel.Creating_wipe = false;
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            af.saveParcelWipe = function(wipeTest, copy, wipe) {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedWipe){
                            returnedWipe = modelInflatorFactory.instateAllObjectsFromJson( returnedWipe );
                            if(wipe){
                                angular.extend(wipe, copy)
                            }else{
                                dataStoreManager.store(returnedWipe);
                                wipeTest.Parcel_wipes.push(returnedWipe);
                                copy = {};
                            }
                            wipe.edit = false;
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            af.saveParcelWipes = function( test ) {
                af.clearError();
                return $rootScope.SavingSmears = genericAPIFactory.save( test, 'saveParcelWipes' )
                    .then(
                        function(returnedWipes){
                            returnedWipes = modelInflatorFactory.instateAllObjectsFromJson( returnedWipes.data );
                            dataStoreManager.store(returnedWipes);
                            test.loadParcel_wipes();
                            test.adding = false;
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            /* Miscellaneous Wipe Tests */
            af.getAllMiscellaneousWipeTests = function(){
                return dataSwitchFactory.getAllObjects('MiscellaneousWipeTest', true);
            }

            af.saveMiscellaneousWipeTest = function(test) {
                af.clearError();
                return this.save( test )
                    .then(
                        function(returnedMWT){
                            returnedMWT = modelInflatorFactory.instateAllObjectsFromJson( returnedMWT );
                            if(test.Key_id){
                                angular.extend(test, returnedMWT);
                            }else{
                                //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
                                if(!returnedMWT.Miscellaneous_wipes)returnedMWT.Miscellaneous_wipes = [];
                                var i = 10
                                while(i--){
                                    var miscellaneousWipe = new window.MiscellaneousWipe();
                                    miscellaneousWipe.Miscellaneous_wipe_test_id = returnedMWT.Key_id;
                                    miscellaneousWipe.Class = "MiscellaneousWipe";
                                    miscellaneousWipe.edit = true;
                                    returnedMWT.Miscellaneous_wipes.push(miscellaneousWipe);
                                }

                                returnedMWT = modelInflatorFactory.instateAllObjectsFromJson( returnedMWT );
                                dataStoreManager.store(returnedMWT);
                                returnedMWT.adding = true;
                            }
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            af.saveMiscellaneousWipe = function(wipeTest, copy, wipe) {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedWipe){
                            returnedWipe = modelInflatorFactory.instateAllObjectsFromJson( returnedWipe );
                            if(wipe){
                                angular.extend(wipe, copy)
                            }else{
                                dataStoreManager.store(returnedWipe);
                                wipeTest.Miscellaneous_wipes.push(returnedWipe);
                                copy = {};
                            }
                            wipe.edit = false;
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            af.saveMiscellaneousWipes = function( test ) {
                af.clearError();
                return  $rootScope.SavingSmears = genericAPIFactory.save( test, 'saveMiscellaneousWipes' )
                    .then(
                        function(returnedWipes){
                            returnedWipes = modelInflatorFactory.instateAllObjectsFromJson( returnedWipes.data );
                            dataStoreManager.store(returnedWipes);
                            test.loadMiscellaneous_wipes();
                            test.adding = false;
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            /* Miscellaneous Wipe Tests */
            af.getAllMiscellaneousWipeTests = function(){
                return dataSwitchFactory.getAllObjects('MiscellaneousWipeTest', true);
            }

            /* Inspection Wipes */
            af.getInspectionById = function(id){
               return dataSwitchFactory.getObjectById("Inspection", id, true);
            }

            af.saveInspectionWipeTest = function(copy, test, inspection)
            {
                af.clearError();
                if(!copy){
                    copy = new window.InspectionWipeTest();
                    copy.Inspection_id = inspection.Key_id;
                }

                return this.save( copy )
                    .then(
                        function(returnedIWT){
                            returnedIWT = modelInflatorFactory.instateAllObjectsFromJson( returnedIWT );
                            if(test){
                                angular.extend(wipe, copy)
                            }else{
                                returnedIWT = modelInflatorFactory.instateAllObjectsFromJson( returnedIWT );
                                if(!inspection.Inspection_wipe_tests)inspection.Inspection_wipe_tests = [];
                                //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
                                if(!returnedIWT.Inspection_wipes)returnedIWT.Inspection_wipes = [];
                                var i = 10
                                while(i--){
                                    var inspectionWipe = new window.InspectionWipe();
                                    inspectionWipe.Inspection_wipe_test_id = returnedIWT.Key_id;
                                    inspectionWipe.Class = "InspectionWipe";
                                    inspectionWipe.edit = true;
                                    returnedIWT.Inspection_wipes.push(inspectionWipe);
                                }
                                returnedIWT.Inspection_wipes[0].Location = "Background";
                                returnedIWT.adding = true;
                                inspection.Inspection_wipe_tests = [];
                                inspection.Inspection_wipe_tests.push(returnedIWT);
                                dataStoreManager.store(returnedIWT);
                                return returnedIWT;
                            }
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }

            af.saveInspectionWipe = function(copy, wipe, wipeTest)
            {
                af.clearError();
                return this.save( copy )
                    .then(
                        function(returnedWipe){
                            returnedWipe = modelInflatorFactory.instateAllObjectsFromJson( returnedWipe );
                            if(wipe.Key_id){
                                angular.extend(wipe, returnedWipe);

                                //if this is the background wipe, set the parent wipe's background level and lab background level
                                if(wipe.Location == "Background"){
                                    var parent = dataStoreManager.getById("InspectionWipeTest", wipe.Inspection_wipe_test_id);
                                    parent.Background_level = wipe.Curie_level;
                                    parent.Lab_background_level = wipe.Lab_curie_level;
                                }
                            }else{
                                dataStoreManager.store(returnedWipe);
                                wipeTest.Inspection_wipes.push(returnedWipe);
                                var i = wipeTest.Inspection_wipes.length;
                                while(i--){
                                    if(!wipeTest.Inspection_wipes[i].Key_id)wipeTest.Inspection_wipes.splice(i,1);
                                }
                                $rootScope.InspectionWipeCopy = {};
                            }
                            wipe.edit = false;
                        },
                        af.setError('The Wipe Test could not be saved')
                    )
            }


            af.saveInspectionWipes = function( test ) {
                af.clearError();
                return  $rootScope.SavingSmears = genericAPIFactory.save( test, 'saveInspectionWipes' )
                    .then(
                        function(returnedWipes){
                            returnedWipes = modelInflatorFactory.instateAllObjectsFromJson( returnedWipes.data );
                            dataStoreManager.store(returnedWipes);
                            test.loadInspection_wipes();
                            test.adding = false;

                            //set the background_level for the parent inspection wipe
                            var parent = dataStoreManager.getById("InspectionWipeTest", returnedWipes[0].Inspection_wipe_test_id);
                            var i = returnedWipes.length;
                            while(i--){
                                if(returnedWipes[i].Location == "Background"){
                                    parent.Background_level = returnedWipes[i].Curie_level;
                                }
                            }

                        },
                        af.setError('The Wipe Test could not be saved')
                    )
             }

            af.getAllScintVialCollections = function(){
                return dataSwitchFactory.getAllObjects('ScintVialCollection');
            }

            /************************************************
            **
            **          DISPOSALS
            **
            ************************************************/
            af.saveDrum = function(drum,copy){
                af.clearError();

                this.save(copy)
                    .then(
                        function(returnedDrum){
                            returnedDrum = modelInflatorFactory.instateAllObjectsFromJson( returnedDrum );
                            if(drum && drum.Key_id){
                                angular.extend(drum, copy)
                                drum.edit = false;

                            }else{
                                dataStoreManager.store(returnedDrum);
                                $rootScope.DrumCopy = {};
                            }
                        },
                        af.setError('The Drum could not be saved')

                    )
            }

            af.saveWasteBag = function(bag, copy){
              af.clearError();
                return this.save(copy)
                    .then(
                        function(returnedBag){
                            returnedBag = modelInflatorFactory.instateAllObjectsFromJson( returnedBag );
                            if(bag.Key_id){
                                angular.extend(bag, copy)
                            }else{
                                dataStoreManager.store(returnedBag);
                                $rootScope.WasteBagCopy = {};
                            }
                            return returnedBag;
                        },
                        af.setError('The Drum could not be saved')

                    )
            }

            af.saveSVCollection = function(collection, copy){
              af.clearError();
                return this.save(copy)
                    .then(
                        function(returnedCollection){
                            returnedCollection = modelInflatorFactory.instateAllObjectsFromJson( returnedCollection );
                            if(collection.Key_id){
                                angular.extend(collection, copy)
                            }else{
                                dataStoreManager.store(returnedCollection);
                                $rootScope.ScintVialCollectionCopy = {};
                            }
                            return returnedCollection;
                        },
                        af.setError('The Drum could not be saved')

                    )
            }
            
            af.saveCarboyReadingAmount = function (cycle, copy) {

                af.clearError();
                copy.Date_read = convenienceMethods.setMysqlTime(new Date());
                return $rootScope.saving = this.save(copy)
                    .then(
                        function (returnedCycle) {
                            console.log(returnedCycle);
                            returnedCycle = modelInflatorFactory.instateAllObjectsFromJson( returnedCycle );
                            var i = returnedCycle.Carboy_reading_amounts.length;
                            while(i--){
                                if(dataStoreManager.getById("CarboyReadingAmount", returnedCycle.Carboy_reading_amounts[i].Key_id)){
                                    var reading = dataStoreManager.getById("CarboyReadingAmount", returnedCycle.Carboy_reading_amounts[i].Key_id);
                                    angular.extend(reading, returnedCycle.Carboy_reading_amounts[i]);
                                }else{
                                    var reading = modelInflatorFactory.instateAllObjectsFromJson( returnedCycle.Carboy_reading_amounts[i]);
                                    dataStoreManager.store(reading);
                                    reading = dataStoreManager.getById("CarboyReadingAmount", reading.Key_id);
                                    cycle.Carboy_reading_amounts.push(reading);
                                }
                                reading.edit = false;
                                $rootScope.CarboyReadingAmountCopy = {};
                                reading.loadIsotope();
                            }

                            //remove the edited copy
                            var i = cycle.Carboy_reading_amounts.length;
                            while(i--){
                                if(!cycle.Carboy_reading_amounts[i].Key_id){
                                    cycle.Carboy_reading_amounts.splice(i,1);
                                }
                            }
                            cycle.Pour_allowed_date = returnedCycle.Pour_allowed_date;
                            return cycle;
                        },
                        af.setError('The reading could not be saved')

                    )
            }

            //use this method to loop through a collection of child objects returned from the server and update the cached copies of them
            af.updateChildren = function(obj, childProp){
                var i = obj[childProp].length;
                while(i--){
                    //to do angular.extend the right local obj from the servers copy
                    if(dataStoreManager.getById(obj[childProp][i].Class,obj[childProp][i].Key_id)){
                        var cachedObj = dataStoreManager.getById(obj[childProp][i].Class,obj[childProp][i].Key_id);
                        for(var prop in cachedObj){
                            if(obj[childProp][i][prop])obj[childProp][i][prop] = obj[childProp][i][prop];
                            obj[childProp][i] = cachedObj[prop];
                        }
                    }
                }
            }


            /********************************************************************************
            **
            **      QUARTERLY INVENTORIES
            **
            *********************************************************************************/
            af.createQuarterlyInventory = function($endDate, $dueDate){
 
                var endDate = convenienceMethods.setMysqlTime(af.getDate($endDate));
                var dueDate = convenienceMethods.setMysqlTime(af.getDate($dueDate));

                var urlSegment = "createQuarterlyInventories&endDate="+endDate+"&dueDate="+dueDate;
                return genericAPIFactory.read( urlSegment )
                        .then(
                            function(returned){
                                return  modelInflatorFactory.instateAllObjectsFromJson( returned.data );
                            }
                        )
            }

            af.getMostRecentInventory = function(){
                af.clearError();
                var urlSegment = 'getMostRecentInventory';
                return genericAPIFactory.read( urlSegment )
                    .then(
                        function(returned){
                            var returnedInventory = modelInflatorFactory.instateAllObjectsFromJson( returned.data );
                            dataStoreManager.store(returnedInventory);
                            return dataStoreManager.getById("QuarterlyInventory", returnedInventory.Key_id);
                        },
                        af.setError('The Quarterly Inventory could not be retrieved.')
                    )
            }
            af.getQuartleryInventoriesByDateRange = function(startDate, endDate){

            }

            af.saveQuarterlyInventory = function( inventory, copy ){
                af.clearError();
                return this.save(copy)
                    .then(
                        function(returnedInventory){
                            returnedInventory = modelInflatorFactory.instateAllObjectsFromJson( returnedInventory );
                            if(drum.Key_id){
                                angular.extend(inventory, returnedInventory)
                            }else{
                                dataStoreManager.store(returnedInventory);
                            }
                            $rootScope.QuarterlyInventory = {};
                            return returnedInventory;
                        },
                        af.setError('The Quarterly Inventory could not be saved')

                    )
            }

            af.getInventoriesByPiId = function(id){
                af.clearError();
                var urlSegment = 'getInventoriesByPiId&piId=' + id;
                return genericAPIFactory.read(urlSegment)
                        .then(
                            function(returned){
                                var inventories = modelInflatorFactory.instateAllObjectsFromJson( returned.data );
                                store.store(inventories);
                                return inventories;
                            },
                            af.setError("Couldn't get Quarterly Inventories for the selected Principal Investigator.")
                        )
            }

            af.getPIInventoryIdById = function(id){
                af.clearError();
                var urlSegment = 'getPIInventoryIdById&piId=' + id;
                return genericAPIFactory.read(urlSegment)
                        .then(
                            function(returned){
                                var inventories = modelInflatorFactory.instateAllObjectsFromJson( returned.data );
                                dataStoreManager.store(inventories);
                                return inventories;
                            },
                            af.setError("Couldn't get Quarterly Inventories for the selected Principal Investigator.")
                        )
            }

            af.getQuartleryInventory = function(piKeyid)
            {
                var urlSegment = 'getCurrentPIInventory&piId=' + piKeyid;
                return genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            var inventory = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store(inventory);
                            return inventory;
                        });
            }

            af.savePiQuarterlyInventory = function(inventory, copy)
            {
                af.clearError();
                copy.Sign_off_date = convenienceMethods.setMysqlTime(new Date());
                return this.save(copy)
                    .then(
                        function(returnedPIQ){
                            inventory.Sign_off_date = returnedPIQ.Sign_off_date;
                            return returnedPIQ;
                        },
                        af.setError('The Inventory could not be saved')

                    )
            }

            af.savePIAuthorization = function(copy, auth, pi){
                copy.Rooms = [];
                copy.Departments = [];
                if(pi.Rooms){
                    var i = pi.Rooms.length;
                    while(i--){
                        if(pi.Rooms[i].isAuthorized)copy.Rooms.push(pi.Rooms[i]);
                    }
                }

                if(pi.Departments){
                    var i = pi.Departments.length;
                    while(i--){
                        if(pi.Departments[i].isAuthorized)copy.Departments.push(pi.Departments[i]);
                    }
                }

                af.clearError();
                return this.save(copy)
                    .then(
                        function(returnedAuth){
                            returnedAuth = modelInflatorFactory.instateAllObjectsFromJson( returnedAuth );
                            if(copy.Key_id){
                                angular.extend(auth, copy);
                                auth.Rooms = copy.Rooms.slice();
                            }else{
                                dataStoreManager.store(returnedAuth);
                                pi.Pi_authorization = returnedAuth;
                            }
                            return returnedAuth;
                        },
                        af.setError('The Quarterly Inventory could not be saved')
                    )
            }

            af.getRadModels = function(){
                     return dataSwitchFactory.getAllObjects("RadModelDto")
                        .then(function (dto) {
                            var dto = dto[0];
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.User ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.Isotope ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.Authorization ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.PIAuthorization ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson(dto.WasteType));
                            //store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.CarboyUseCycle ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson(dto.CarboyReadingAmount));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson(dto.Carboy));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson(dto.CarboyUseCycle));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.InspectionWipe ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.InspectionWipeTest ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.ParcelWipe ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.ParcelWipeTest ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.ParcelUseAmount ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.Parcel ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.Pickup ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.PurchaseOrder ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.User ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.ScintVialCollection ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.WasteBag ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.SolidsContainer ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson( dto.Room ));
                            store.store(modelInflatorFactory.instateAllObjectsFromJson(dto.PrincipalInvestigator));
                            console.log(dataStore);
                            return dataStore;
                        });
            }

            return af;
        });
