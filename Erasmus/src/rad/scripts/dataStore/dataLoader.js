'use strict';

/**
 *  This class simplifies data accessing, handling checking the Cache first for data, then contacting
 * the API if that item is not present in the cache. Factors out this logic from Entities.
 *
 * @author Perry
 */

// constructor
var dataLoader = {};

/* NOTE
    These methods do not return anything - instead, they set the given property on parent.
    Doing so avoids any asynchronous vs synchronous nastiness.
*/


dataLoader.loadOneToManyRelationship = function( parent, property, relationship ) {
    // methodString overrides default behavior for making special server calls.
    if( relationship.methodString ) {
        var paramValue = '&' + relationship.paramName + '=' + parent[relationship.paramValue];

        parent.api.read( relationship.methodString, paramValue )
            .then(function( returnedPromise ) {
                parent[property] = parent.inflator.instateAllObjectsFromJson( returnedPromise.data );
            });
    }

    // if the required data is already cached get it from there.
    else if( dataLoader[relationship.className]) {
        parent[property] = dataStoreManager.getChildrenByParentProperty(
                relationship.className, relationship.keyReference, parent[relationship.paramValue]);
    }

    // data not cached, get it from the server
    else {
        var urlFragment = parent.api.fetchActionString("getAll", relationship.className);

        parent.rootScope[parent.Class+"sBusy"] = parent.api.read(urlFragment).then(function(returnedPromise) {
            //cache result so we don't hit the server next time
            var instatedObjects = parent.inflator.instateAllObjectsFromJson( returnedPromise.data );
            dataStoreManager.store(instatedObjects);

            parent[property] = dataStoreManager.getChildrenByParentProperty(
                    relationship.className, relationship.keyReference, parent[relationship.paramValue]);
        });
    }
}

dataLoader.loadManyToManyRelationship = function( parent, property, relationship ) {
    // if this type of relationship is cached, use it.
    if( dataLoader[relationship.name] ) {
        var matches = dataLoader.getChildrenByParentProperty(
                relationship.name, relationship.keyReference, parent[relationship.paramValue]);

        var className = relationship.className;
        var idProperty = className + '_id'; // property containing key id of desired item.

        var instatedMatches = dataLoader.instateRelationItems(matches, className, idProperty);
        parent[property] = instatedMatches;
    }
    // data not cached, get from the server
    else {
        var urlFragment = 'getRelationships';
        var paramValue = '&class1=' + parent.Class + '&class2=' + relationship.className;

        parent.api.read( urlFragment, paramValue )
            .then(function( returnedPromise ) {
                //cache result so we don't hit the server next time
                var instatedObjects = parent.inflator.instateAllObjectsFromJson( returnedPromise.data );
                dataStoreManager.store(instatedObjects);

                var matches = dataStoreManager.getChildrenByParentProperty(
                    relationship.name, relationship.keyReference, parent[relationship.paramValue]);

                var className = relationship.className;
                var idProperty = className + '_id'; // property containing key id of desired item.


                // *********************************************
                // BEGIN MESSY AREA TO REFACTOR
                // This could all be replaced with a DataSwitch.getAll call if we had access to it

                // double check that the child class has been loaded, so instateRelationItems has something to instate.
                if(! dataStoreManager.checkCollection(className) ) {
                    console.log(className);
                    console.log('getting rooms');
                    var action = parent.api.fetchActionString('getAll', className);

                    // get data
                    parent.api.read(action).then(function(returnedPromise) {
                        var instatedObjects = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);

                        console.log('returned promise:');
                        console.log(returnedPromise);

                        // add returned data to cache
                        dataStoreManager.store(instatedObjects, true, className);
                        console.log(window.performance.now());

                        // finally instate and set the result on parent.
                        var instatedMatches = dataLoader.instateRelationItems(matches, className, idProperty);
                        console.log('Matches:');
                        console.log(instatedMatches);
                        parent[property] = instatedMatches;
                    });
                }
                else {
                    var instatedMatches = dataLoader.instateRelationItems(matches, className, idProperty);
                    parent[property] = instatedMatches;
                }

                // END MESSY AREA TO REFACTOR
                // ********************************************


            });
    }
}

dataLoader.instateRelationItems = function(relationList, className, keyProperty) {
    var i = relationList.length;
    var instatedItems = [];

    /*
    console.log('relation list:');
    console.log(relationList);
    console.log('className: ' + className);
    */
    while(i--) {
        console.log('relationlist:');
        console.log(relationList);
        console.log(i);
        var id = relationList[i][keyProperty];
        instatedItems.push( dataStoreManager.getById(className, id) );
    }

    return instatedItems;
}

dataLoader.loadObjectById = function( parent, property, className, id ) {

    // check cache first
    if( dataStoreManager.checkCollection(className) ) {
        parent[property] = dataStoreManager.getById(className, id);
    }
    // not cached, get from server
    else {
        var getString = parent.api.fetchActionString("getById", className);
        var idParam = '&id=' + id;
        parent.api.read(getString, idParam).then(function( returnedPromise ) {
            parent[property] = parent.inflator.instateAllObjectsFromJson( returnedPromise.data );
        });
    }
}
