'use strict';

angular
    .module('EquipmentModule')
        .factory('applicationControllerFactory', function applicationControllerFactory(rootApplicationControllerFactory, modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods ){
            var af = rootApplicationControllerFactory;
            var store = dataStoreManager;
            //give us access to this factory in all views.  Because that's cool.
            $rootScope.af = this;

            store.$q = $q;

            af.getViewMap = function(current) {
                var viewMap = [
                    {
                        Name: 'equipment',
                        Label: 'Equipment Center',
                        Dashboard:false
                    },
                    {
                        Name:'equipment.autoclaves',
                        Label: 'Autoclaves',
                        Dashboard: true
                    },
                    {
                        Name:'equipment.bio-safety-cabinets',
                        Label: 'Biological Safety Cabinets',
                        Dashboard: true
                    },
                    {
                        Name:'equipment.chem-fume-hoods',
                        Label: 'Chemical Fume Hoods',
                        Dashboard: true
                    },
                    {
                        Name:'equipment.lasers',
                        Label: 'Lasers',
                        Dashboard: true
                    },
                    {
                        Name:'equipment.x-ray',
                        Label: 'X-Ray Machines',
                        Dashboard: true
                    }
                ];

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
            af.fireModal = function( templateName, object  ) {
                if(object)af.setModalData(object);
                var modalInstance = $modal.open({
                  templateUrl: templateName+'.html',
                  controller: 'GenericModalCtrl'
                });
            }

            af.setModalData = function(thing) {
                dataStoreManager.setModalData(thing);
            }

            af.getModalData = function() {
                return dataStoreManager.getModalData();
            }

            af.deleteModalData = function() {
                dataStore.modalData = [];
            }

            /********************************************************************
            **
            **      AUTOCLAVE            **
            ********************************************************************/

            af.getAutuclaveById = function(key_id) {
                var urlSegment = 'getAutuclaveById&id=' + key_id;

                if( store.checkCollection( 'Autuclave', key_id ) ) {
                    var autoclave = store.getById( 'Autuclave', key_id )
                        .then(function(autoclave) {
                            return autoclave;
                        });
                }
                else {
                    var autoclave = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store autoclave in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return autoclave;
            }

            af.getAllAutoclaves = function(key_id) {
                return dataSwitchFactory.getAllObjects('Autoclave');
            }

            af.saveAutoclave = function(copy, autoclave) {
                af.clearError();
                console.log(copy);
                return this.save(copy)
                    .then(
                        function(returnedAutoclave){
                            returnedAutoclave = modelInflatorFactory.instateAllObjectsFromJson(returnedAutoclave);
                            if(autoclave){
                                angular.extend(autoclave, copy)
                            }else{
                                dataStoreManager.addOnSave(returnedAutoclave);
                                dataStoreManager.store(returnedAutoclave);
                            }
                        },
                        af.setError('The Autoclave could not be saved')
                    )
            }

            /********************************************************************
            **
            **      BioSafetyCabinet            **
            ********************************************************************/

            af.getBioSafetyCabinetById = function(key_id) {
                var urlSegment = 'getBioSafetyCabinetById&id=' + key_id;

                if( store.checkCollection( 'BioSafetyCabinet', key_id ) ) {
                    var bioSafetyCabinet = store.getById( 'BioSafetyCabinet', key_id )
                        .then(function(bioSafetyCabinet) {
                            return bioSafetyCabinet;
                        });
                }
                else {
                    var bioSafetyCabinet = genericAPIFactory.read( urlSegment )
                        .then( function( returnedPromise ) {
                            // store bioSafetyCabinet in cache here?
                            return modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        });
                }
                return bioSafetyCabinet;
            }

            af.getAllBioSafetyCabinets = function() {
                return dataSwitchFactory.getAllObjects('BioSafetyCabinet');
            }

            af.getAllRooms = function() {
                return dataSwitchFactory.getAllObjects('Room');
            }

            af.getAllBuildings = function() {
                return dataSwitchFactory.getAllObjects('Building');
            }

            af.getAllPrincipalInvestigators = function() {
                return dataSwitchFactory.getAllObjects('PrincipalInvestigator');
            }

            af.saveBioSafetyCabinet = function(copy, bioSafetyCabinet) {
                af.clearError();

                //flatten to avoid circular JSON structure
                var secondCopy = {
                            Certification_date: copy.Certification_date,
                            Class: "BioSafetyCabinet",
                            Frequency: copy.Frequency,
                            Is_active: copy.Is_active,
                            Make: copy.Make,
                            Model: copy.Model,
                            Principal_investigator_id: copy.Principal_investigator_id,
                            Report_path: copy.Report_path,
                            Room_id: copy.Room_id,
                            Serial_number: copy.Serial_number,
                            Type: copy.Type,
                }

                if(copy.Key_id){secondCopy.Key_id = copy.Key_id;}

                return this.save(secondCopy)
                    .then(
                        function(returnedBioSafetyCabinet){
                            returnedBioSafetyCabinet = modelInflatorFactory.instateAllObjectsFromJson(returnedBioSafetyCabinet);
                            if(bioSafetyCabinet){
                                angular.extend(dataStoreManager.getById("BioSafetyCabinet",bioSafetyCabinet.Key_id), returnedBioSafetyCabinet);
                                bioSafetyCabinet.loadRoom();
                                bioSafetyCabinet.loadPI();
                            }else{
                                returnedBioSafetyCabinet.loadRoom();
                                bioSafetyCabinet.loadPI();
                                dataStoreManager.addOnSave(returnedBioSafetyCabinet);
                                dataStoreManager.store(returnedBioSafetyCabinet);
                            }
                        },
                        af.setError('The BioSafetyCabinet could not be saved')
                    )
            }

            /********************************************************************
            **
            **		USER MANAGEMENT
            **
            ********************************************************************/

            af.getUserById = function( key_id ) {
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

            af.getAllUsers = function() {
                return dataSwitchFactory.getAllObjects('User');
            }

            af.getUsersViewModel = function() {
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

                    // TODO: Make specific to Equipment module, if this is even needed.
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
            **      HANDY FUNCTIONS
            **
            ********************************************************************/

            af.getDate = function(dateString) {
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

            af.getIsExpired = function(dateString) {
                console.log(dateString)
                console.log(Date.parse(dateString))
                var seconds = Date.parse(dateString);
                console.log(new Date().getTime())
                console.log(seconds < new Date().getTime())
                return seconds < new Date().getTime();
            }

            af.setError = function(errorString) {
                $rootScope.error = errorString + ' please check your internet connection and try again';
            }

            af.clearError = function() {
                $rootScope.error = null;
            }

            //use this method to loop through a collection of child objects returned from the server and update the cached copies of them
            af.updateChildren = function(obj, childProp) {
                var i = obj[childProp].length;
                while(i--){
                    //to do angular.extend the right local obj from the servers copy
                    if(dataStoreManager.getById(obj[childProp][i].Class,obj[childProp][i].Key_id)){
                        var cachedObj = dataStoreManager.getById(obj[childProp][i].Class,obj[childProp][i].Key_id);
                        for(var prop in cachedObj){
                            console.log(obj[childProp][i][prop]);
                            console.log(obj[childProp][i][prop]);
                            if(obj[childProp][i][prop])obj[childProp][i][prop] = obj[childProp][i][prop];
                            obj[childProp][i] = cachedObj[prop];
                        }
                    }
                }
            }


            return af;
        });