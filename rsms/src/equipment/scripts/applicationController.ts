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

            af.save = function (viewModel: FluxCompositerBase): Promise<FluxCompositerBase> {
                $rootScope.error = null;
                return $rootScope.saving = DataStoreManager.save(viewModel);
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

            af.saveAutoclave = function (autoclave: equipment.Autoclave): Promise<FluxCompositerBase> {
                return af.save(autoclave);
            }

            /********************************************************************
            **
            **      X-Ray            **
            ********************************************************************/

            af.saveXRay = function (xray: equipment.XRay): Promise<FluxCompositerBase> {
                return af.save(xray);
            }

            /********************************************************************
            **
            **      Laser            **
            ********************************************************************/

            af.saveLaser = function (laser: equipment.Laser): Promise<FluxCompositerBase> {
                return af.save(laser);
            }

            /********************************************************************
            **
            **      ChemFumeHood            **
            ********************************************************************/

            af.saveChemFumeHood = function (hood: equipment.ChemFumeHood): Promise<FluxCompositerBase> {
                return af.save(hood);
            }
            
            /********************************************************************
            **
            **      EquipmentInspection            **
            ********************************************************************/

            af.saveEquipmentInspection = function (equipmentInspection: equipment.EquipmentInspection): Promise<FluxCompositerBase> {
                return af.save(equipmentInspection);
            }
            
            /********************************************************************
            **
            **      BioSafetyCabinet            **
            ********************************************************************/
            
            af.saveBioSafetyCabinet = function (bioSafetyCabinet: equipment.BioSafetyCabinet): Promise<FluxCompositerBase> {
                return af.save(bioSafetyCabinet);
            }
            
            /********************************************************************
            **
            **		USER MANAGEMENT
            **
            ********************************************************************/

            af.getUsersViewModel = function() {
                    var model = [];
                    var userPromise = $q.defer();
                    var piPromise = $q.defer();
                    var roomsPromise = $q.defer();
                    var campusesPromise = $q.defer();
                    var relationsPromise = $q.defer();
                    var all = $q.all([userPromise.promise,relationsPromise.promise,piPromise.promise,roomsPromise.promise,campusesPromise.promise])

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

                    this.getAllCampuses()
                            .then(
                                function (campuses) {
                                    campusesPromise.resolve(campuses);
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
