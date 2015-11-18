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


dataLoader.loadOneToManyRelationship = function (parent, property, relationship, whereClause, recurse) {
    if(!recurse)recurse = false;
    // methodString overrides default behavior for making special server calls.
    if (relationship.methodString) {

        var paramValue = '&' + relationship.paramName + '=' + parent[relationship.paramValue];

        parent.api.read(relationship.methodString, paramValue)
            .then(function (returnedPromise) {
                parent[property] = parent.inflator.instateAllObjectsFromJson(returnedPromise.data, null, recurse);
            });
    }

    // if the required data is already cached get it from there.
    else if (dataStore[relationship.className]) {
        if (!whereClause) whereClause = false;
        parent[property] = [];
        parent[property] = dataStoreManager.getChildrenByParentProperty(
            relationship.className, relationship.keyReference, parent[relationship.paramValue], whereClause);
    }

    // data not cached, get it from the server
    else {
        var urlFragment = parent.api.fetchActionString("getAll", relationship.className);

        parent.rootScope[parent.Class + "sBusy"] = parent.api.read(urlFragment).then(function (returnedPromise) {
            //cache result so we don't hit the server next time
            var instatedObjects = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
            dataStoreManager.store(instatedObjects);
            parent[property] = dataStoreManager.getChildrenByParentProperty(
                relationship.className, relationship.keyReference, parent[relationship.paramValue]);

            if(recurse)dataLoader.recursivelyInstantiate(instatedObjects, parent);

        });
    }
}
/*
dataLoader.loadManyToManyRelationship = function (parent, property, relationship, apiOverride) {
    // if this type of relationship is cached, use it.
    if (dataLoader[relationship.name]) {
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
        if(apiOverride)urlFragment = apiOverride;
        parent.api.read(urlFragment, paramValue)
            .then(function (returnedPromise) {
                //cache result so we don't hit the server next time
                var instatedObjects = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
                dataStoreManager.store(instatedObjects);

                var matches = dataStoreManager.getChildrenByParentProperty(
                    relationship.name, relationship.keyReference, parent[relationship.paramValue]);

                var className = relationship.className;
                var idProperty = relationship.otherKey; // property containing key id of desired item.


                // *********************************************
                // BEGIN MESSY AREA TO REFACTOR
                // This could all be replaced with a DataSwitch.getAll call if we had access to it

                // double check that the child class has been loaded, so instateRelationItems has something to instate.
                if (!dataStoreManager.checkCollection(className)) {
                    var action = parent.api.fetchActionString('getAll', className);

                    // get data
                    parent.api.read(action).then(function (returnedPromise) {
                        var instatedObjects = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
                        console.log(instatedObjects);
                        // add returned data to cache
                        dataStoreManager.store(instatedObjects, true, className);

                        // finally instate and set the result on parent.
                        var instatedMatches = dataLoader.instateRelationItems(matches, className, idProperty);
                        parent[property] = instatedMatches;
                    });
                } else {
                    var instatedMatches = dataLoader.instateRelationItems(matches, className, idProperty);
                    parent[property] = instatedMatches;
                }

                // END MESSY AREA TO REFACTOR
                // ********************************************


            });
    }
}
*/

dataLoader.loadManyToManyRelationship = function(parent, relationship){
    if(dataStoreManager.checkCollection(relationship.childClass) && dataStore[relationship.table]){
        parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
    } // data not cached, get from the server
    else {
        var urlFragment = 'getRelationships';
        var paramValue = '&class1=' + parent.Class + '&class2=' + relationship.childClass;
        parent.api.read(urlFragment+paramValue)
            .then(function (returnedPromise) {
                //cache result so we don't hit the server next time
                dataStoreManager.storeGerunds(returnedPromise.data, relationship.table);

                var className = relationship.childClass;


                // *********************************************
                // BEGIN MESSY AREA TO REFACTOR
                // This could all be replaced with a DataSwitch.getAll call if we had access to it

                // double check that the child class has been loaded, so instateRelationItems has something to instate.
                if (!dataStoreManager.checkCollection(className)) {
                    var action = parent.api.fetchActionString('getAll', className+'s');

                    // get data
                    parent.api.read(action).then(function (returnedPromise) {
                        var instatedObjects = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
                        console.log(instatedObjects);
                        // add returned data to cache
                        dataStoreManager.store(instatedObjects, true, className);

                        // finally instate and set the result on parent.
                        var instatedMatches = dataLoader.instateRelationItems(matches, className, idProperty);
                        parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
                    });
                } else {
                   parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);

                }

                // END MESSY AREA TO REFACTOR
                // ********************************************


            });
    }
}

dataLoader.instateRelationItems = function (relationList, className, keyProperty) {
    var i = relationList.length;
    var instatedItems = [];

    while (i--) {
        var id = relationList[i][keyProperty];
        instatedItems.push(dataStoreManager.getById(className, id));
    }

    return instatedItems;
}

dataLoader.loadChildObject = function (parent, property, className, id) {

    // check cache first
    if (dataStoreManager.checkCollection(className)) {
        parent[property] = dataStoreManager.getById(className, id);
    }
    // not cached, get from server
    else {
        var getString = parent.api.fetchActionString("getById", className);
        var idParam = '&id=' + id;
        parent.api.read(getString, idParam).then(function (returnedPromise) {
            parent[property] = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
        });
    }
}

dataLoader.loadChildObjectByParentProperty = function (parent, property, className, int, childProperty, getString) {
    console.log(className);
    // check cache first
    if (dataStoreManager.checkCollection(className)) {
        parent[property] = dataStoreManager.getChildByParentProperty(className, childProperty, int);
    }
    // not cached, get from server
    else {
        var idParam = '&id=' + int;
        parent.api.read(getString, idParam).then(function (returnedPromise) {
            parent[property] = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
        });
    }
}

dataLoader.recursivelyInstantiate = function(instatedObjects, parent){
    console.log(instatedObjects);
    var i = instatedObjects.length;
    while(i--){
        for(var prop in instatedObjects[i]){

            if( instatedObjects[i][prop] instanceof Array  && instatedObjects[i][prop].length && instatedObjects[i][prop][0].Class && window[instatedObjects[i][prop][0].Class] && !(instatedObjects[i][prop][0] instanceof window[instatedObjects[i][prop][0].Class])){
                instatedObjects[i][prop] = parent.inflator.instateAllObjectsFromJson(instatedObjects[i][prop]);
                dataStoreManager.store(instatedObjects[i][prop]);
                dataLoader.recursivelyInstantiate(instatedObjects[i][prop]);
            }
            //it's an object with a property called class, there is a client-side class of type object.class, object is not yet an instance of that class
            else if( typeof instatedObjects[i][prop] == "object" && instatedObjects[i][prop] != null && instatedObjects[i][prop].Class && window[instatedObjects[i][prop].Class] && !(instatedObjects[i][prop] instanceof window[instatedObjects[i][prop].Class])){
                instatedObjects[i][prop] = parent.inflator.instantiateObjectFromJson(instatedObjects[i][prop]);
            }
        }
    }
}
