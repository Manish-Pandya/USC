'use strict';

angular
    .module('rootApplicationController',[])

        .factory('rootApplicationControllerFactory', function actionFunctionsFactory( modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods ){

            /********************************************************************
            **
            **      CLIENT MANAGEMENT CONVENIENCE
            **
            ********************************************************************/
            var rac = {};

            rac.copy = function( object )
            {
                    store.createCopy( object );
                    //set the other objects in this one's collection to the non-edit state
                    store.setEditStates( object );
            }

            rac.createCopy = function(obj)
            {
                obj.edit = true;
                $rootScope[obj.Class+'Copy'] = dataStoreManager.createCopy(obj);
                return $rootScope[obj.Class+'Copy'];
            }

            rac.cancelEdit = function( obj )
            {
                    obj.edit = false;
                    $rootScope[obj.Class+'Copy'] = {};
                    //store.replaceWithCopy( object );
            }

            rac.setObjectActiveState = function( object )
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

            rac.save = function( object, saveChildren, url )
            {
                    if(!saveChildren)saveChildren = false;
                    if(!url)url = false

                    var defer = $q.defer();
                    //set a root scope marker as the promise so that we can use angular-busy directives in the view
                    $rootScope[object.Class+'Saving'] = genericAPIFactory.save( object, url, saveChildren )
                        .then(
                            function( returnedData ){
                                defer.resolve(returnedData.data);
                            },
                            function( error )
                            {
                                defer.reject(error);
                                $rootScope.error = 'error';
                            }
                        );
                    return defer.promise;
            }

            rac.getById = function( objectFlavor, key_id )
            {
                return store.getById(objectFlavor, key_id );
            }

            rac.getAll = function(className) {
                return dataSwitchFactory.getAllObjects(className);
            }

            rac.getCachedCollection = function(flavor)
            {
                return dataStore[flavor];
            }

            rac.setError = function(errorString, editedThing)
            {
                $rootScope.error = errorString + ' Please check your internet connection and try again';
                if(editedThing)editedThing.edit = false;
            }

            rac.clearError = function()
            {
                $rootScope.error = null;
            }
            
            /********************************************************************
            **
            **      MODALS
            **
            ********************************************************************/
            rac.fireModal = function(templateName, object)
            {
                console.log("Firing modal...", object);
                if(object) rac.setModalData(object);
                var modalInstance = $modal.open({
                  templateUrl: templateName+'.html',
                  controller: 'GenericModalCtrl'
                });
            }

            rac.setModalData = function(thing)
            {
                dataStoreManager.setModalData(thing);
            }

            rac.getModalData = function()
            {
                return dataStoreManager.getModalData();
            }

            rac.deleteModalData = function()
            {
                dataStore.modalData = [];
            }
            
            /********************************************************************/

            return rac;
});
