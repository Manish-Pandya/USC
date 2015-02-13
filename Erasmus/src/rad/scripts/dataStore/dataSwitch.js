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

            //TODO generic getObject method


            dataSwitch.getAllObjects = function( className ) {

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
                        console.log('in dataswitch api branch')
                        var action = genericAPIFactory.fetchActionString('getAll', className);

                        // get data
                        genericAPIFactory.read(action).then(function(returnedPromise) {
                            var instatedObjects = modelInflatorFactory.instateAllObjectsFromJson(returnedPromise.data);
                            deferred.resolve(instatedObjects);

                            // add returned data to cache
                            dataStoreManager.store(instatedObjects, true, className);
                        });

                    }

                    return deferred.promise;
                }
            }
            return dataSwitch;
        });
