'use strict';

/**
 * This class simplifies data loading on entities. It checks the cache first for data, then contacts
 * the API if that item is not present in the cache.
 *
 * @author Perry
 */

angular
    .module("dataSwitchModule", [])
        .factory("dataSwitchFactory", function dataSwitchFactory(genericAPIFactory, modelInflatorFactory, $q, $rootScope) {

            // constructor
            var dataSwitch = {};

            // cache of requests
            dataSwitch.promises = [];

            /**
             * Given a parent entity to operate on, will load given property from the
             *  datastore or API and set it to the parent entity.
             *
             * @param parent       - entity to operate on
             * @param property     - string of the name of the property to set on the parent
             * @param relationship - object containing details of relationship
             */
            dataSwitch.getChildObject = function( parent, property, relationship )
            {
                    // this method should always return a promise
                    var deferred = $q.defer();

                    // check cache first
                    if( dataStoreManager.checkCollection(relationship.className) ) {
                        deferred.resolve(
                            dataStoreManager.getChildrenByParentProperty(
                                relationship.className,
                                relationship.keyReference,
                                parent[relationship.paramValue]
                            )
                        );
                    }
                    // if not in cache, get from server
                    else {

                        // prepare url to send (TODO shoudl API handle this?)
                        var urlFragment = relationship.methodString;
                        var paramValue = '&' + relationship.paramName + '=' + parent[relationship.paramValue];

                        // read object from server
                        $rootScope[parent.Class+"sBusy"] = genericAPIFactory.read( urlFragment, paramValue )
                            .then(function( returnedPromise ) {
                                deferred.resolve( modelInflatorFactory.instateAllObjectsFromJson(returnedPromise.data) );
                            });
                    }

                    return deferred.promise;
            }

            dataSwitch.getAllObjects = function( className, recurse ) {

                // should always return a promise
                var deferred = $q.defer();

                //this request has already been made.  we return the promise already created by it instead of making another
                if( dataSwitch.promises[className] ){
                    return dataSwitch.promises[className].promise;
                }else{
                    //this is a new request.  make reference to our promise in dataSwitch so the next time we make it, we return the one we already made
                    dataSwitch.promises[className] = deferred;
                    // check cache first
                    if( dataStoreManager.checkCollection(className) ) {
                        deferred.resolve( dataStoreManager.get(className) );
                    }
                    // if not in cache, get from server
                    else {

                        //prepare url fragment to send
                        var action = genericAPIFactory.fetchActionString('getAll', className);

                        // get data
                        genericAPIFactory.read(action).then(function(returnedPromise) {
                            var instatedObjects = modelInflatorFactory.instateAllObjectsFromJson(returnedPromise.data);
                            
                            if(recurse){
                                dataSwitch.recursivelyInstantiate(instatedObjects);
                            }

                            deferred.resolve(instatedObjects);

                            // add returned data to cache
                            dataStoreManager.store(instatedObjects, true);
                        });
                    }

                    return deferred.promise;
                }
            }

            dataSwitch.getObjectById = function(className, id, recurse, queryParam) {

                // should always return a promise
                var deferred = $q.defer();

                if( dataSwitch.promises[className] ) {
                    return dataSwitch.promises[className].promise;
                }
                else {
                    dataSwitch.promises[className] = deferred;
                    //check cache first
                    if( dataStoreManager.checkCollection(className) ) {
                        deferred.resolve( dataStoreManager.getById(className, id) );
                    }
                    else {
                        if(!queryParam)queryParam = false;
                        var action = genericAPIFactory.fetchActionString('getById', className, queryParam);

                        action += '&id=' + id;

                        // get data
                        genericAPIFactory.read(action).then(function(returnedPromise) {
                            var instatedObjects = modelInflatorFactory.instateAllObjectsFromJson(returnedPromise.data);
                            dataStoreManager.store(instatedObjects);
                            deferred.resolve(instatedObjects);
                            if(recurse){
                                dataSwitch.recursivelyInstantiate([instatedObjects]);
                            }
                            // TODO should we cache individually-loaded things?
                            //console.log('NOTE: Recieved object of flavor ' + className +
                                    //' and id ' + id + ', but not caching it');
                        });

                    }

                }
                return deferred.promise;
            }

            dataSwitch.recursivelyInstantiate = function(instatedObjects){
                var i = instatedObjects.length;
                while(i--){
                    for(var prop in instatedObjects[i]){
                        if( instatedObjects[i][prop] instanceof Array  && instatedObjects[i][prop][0] && instatedObjects[i][prop][0].Class && window[instatedObjects[i][prop][0].Class] ){
                            dataStoreManager.store(instatedObjects[i][prop]);
                            instatedObjects[i][prop] = modelInflatorFactory.instateAllObjectsFromJson(instatedObjects[i][prop]);
                            dataSwitch.recursivelyInstantiate(instatedObjects[i][prop]);
                        }
                    }
                }
            }

            return dataSwitch;
        });
