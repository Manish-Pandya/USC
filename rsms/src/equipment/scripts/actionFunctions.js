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

            af.copy = function( obj ) {
                    store.createCopy( obj );
                    //set the other objects in this one's collection to the non-edit state
                    store.setEditStates( obj );
            }

            af.createCopy = function(obj) {
                obj.edit = true;
                $rootScope[obj.Class+'Copy'] = dataStoreManager.createCopy(obj);
            }

            af.cancelEdit = function( obj ) {
                    obj.edit = false;
                    $rootScope[obj.Class+'Copy'] = {};
                    //store.replaceWithCopy( object );
            }

            af.setObjectActiveState = function( object ) {
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

            af.save = function( object, saveChildren ) {
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

            af.getById = function( objectFlavor, key_id ) {
                return store.getById(objectFlavor, key_id);
            }

            af.getAll = function(className) {
                return dataSwitchFactory.getAllObjects(className);
            }

            af.getCachedCollection = function(flavor) {
                return dataStore[flavor];
            }

            af.getViewMap = function(current) {
                var viewMap = [
                    {
                        Name: 'equipment-home',
                        Label: 'Equipment Center',
                        Dashboard:false
                    },
                    {
                        Name:'autoclaves',
                        Label: 'Autoclaves',
                        Dashboard: true
                    },
                    {
                        Name:'bio-safety-cabinets',
                        Label: 'Biological Safety Cabinets',
                        Dashboard: true
                    },
                    {
                        Name:'chem-fume-hoods',
                        Label: 'Chemical Fume Hoods',
                        Dashboard: true
                    },
                    {
                        Name:'lasers',
                        Label: 'Lasers',
                        Dashboard: true
                    },
                    {
                        Name:'x-ray',
                        Label: 'X-Ray Machines',
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
