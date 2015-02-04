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

                // check cache first
                if( dataStoreManager.checkCollection(className) ) {
                    console.log('not hitting server');
                    deferred.resolve( dataStoreManager.get(className) );
                }
                // if not in cache, get from server
                else {

                    //prepare url fragment to send
                    var action = genericAPIFactory.fetchActionString('getAll', className);
                    console.log('URL: ' + action);

                    // get data
                    genericAPIFactory.read(action).then(function(returnedPromise) {
                        var object = modelInflatorFactory.instateAllObjectsFromJson(returnedPromise.data);
                        console.log('OBJECT:');
                        console.log(object);
                        deferred.resolve(object);
                    });

                    /*
                    FOR TOMORROW:
                    add returned data to dataStore
                        dataStoreManager.store or .addToCollection?

                    */

                }

                return deferred.promise;
            }
            return dataSwitch;
        });
