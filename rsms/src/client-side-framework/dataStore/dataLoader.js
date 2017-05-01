'use strict';

/**
 *  This class simplifies data accessing, handling checking the Cache first for data, then contacting
 * the API if that item is not present in the cache. Factors out this logic from Entities.
 *
 * @author Perry
 */

// constructor
var dataLoader = {};
dataLoader.promiseMatrix = {};

/* NOTE
    These methods do not return anything - instead, they set the given property on parent.
    Doing so avoids any asynchronous vs synchronous nastiness.
*/


dataLoader.loadOneToManyRelationship = function (parent, property, relationship, whereClause, recurse) {
    if (!recurse) recurse = false;
    // methodString overrides default behavior for making special server calls.
    if (!relationship) console.log(parent, property);
    if (relationship.methodString) {

        var paramValue = '&' + relationship.paramName + '=' + parent[relationship.paramValue];
        return parent.rootScope["loading"] = parent.rootScope[parent.Class + "sBusy"] = parent.api.read(relationship.methodString, paramValue)
            .then(function (returnedPromise) {
                parent[property] = parent.inflator.instateAllObjectsFromJson(returnedPromise.data, null, recurse);
            });
    }

    // if the required data is already cached get it from there.
    else if (!parent[property] && dataStore[relationship.className] && dataStore[relationship.className].length) {
        var d = parent.$q.defer();
        if (!relationship.where) {
            whereClause = false;
        } else {
            whereClause = relationship.where
        }
        parent[property] = [];
        parent[property] = dataStoreManager.getChildrenByParentProperty(
            relationship.className, relationship.keyReference, parent[relationship.paramValue], whereClause);
        d.resolve(parent[property]);
        return d.promise;
    }

    // data not cached, get it from the server, unless it was eagerly loaded on the server
    else {

        //the collection is present in the parent, but the objects have not been placed in the dataStore
        if (parent[property] && parent[property].length) {
            var d = parent.$q.defer();
            if (!relationship.where) {
                whereClause = false;
            } else {
                whereClause = relationship.where
            }
            dataStoreManager.store(parent.inflator.instateAllObjectsFromJson(parent[property]), relationship.className);
            parent[property] = dataStoreManager.getChildrenByParentProperty(
                    relationship.className, relationship.keyReference, parent[relationship.paramValue], whereClause);

            if (recurse) dataLoader.recursivelyInstantiate(instatedObjects, parent);
            d.resolve(parent[property]);
            return d.promise;

        }
        else if (parent.rootScope[parent.Class + "sBusy"]) {
            return parent.rootScope[parent.Class + "sBusy"].then(function (returnedPromise) {
                if (!returnedPromise) {
                    parent[property] = [];
                    return;
                }

                var instatedObjects = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
                dataStoreManager.store(instatedObjects);
                if (!relationship.where) {
                    whereClause = false;
                } else {
                    whereClause = relationship.where
                }

                parent[property] = dataStoreManager.getChildrenByParentProperty(
                    relationship.className, relationship.keyReference, parent[relationship.paramValue], whereClause);

                if (recurse) dataLoader.recursivelyInstantiate(instatedObjects, parent);
                return parent[property];
            })
        }
        else {
            var urlFragment = parent.api.fetchActionString("getAll", relationship.className);
            return parent.rootScope["loading"] = parent.rootScope[parent.Class + "sBusy"] = dataStore['loading' + relationship.table] = parent.api.read(urlFragment).then(function (returnedPromise) {
                //cache result so we don't hit the server next time
                if (!returnedPromise) {
                    parent[property] = [];
                    return;
                }

                var instatedObjects = returnedPromise[0] && returnedPromise[0].api ? returnedPromise : parent.inflator.instateAllObjectsFromJson(returnedPromise);
                dataStoreManager.store(instatedObjects);
                if (!relationship.where) {
                    whereClause = false;
                } else {
                    whereClause = relationship.where
                }

                parent[property] = dataStoreManager.getChildrenByParentProperty(
                    relationship.className, relationship.keyReference, parent[relationship.paramValue], whereClause);

                if (recurse) dataLoader.recursivelyInstantiate(instatedObjects, parent);
                return parent[property];

            });
        }
    }
}

dataLoader.loadManyToManyRelationship = function (parent, relationship) {
    if (dataStoreManager.checkCollection(parent.Class) && dataStoreManager.checkCollection(relationship.table)) {
        var d = parent.$q.defer();
        parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
        d.resolve(relationship.parentProperty);
        return d.promise;
    }
    // data not cached, but we already requested it from the server
    else if(dataStore['loading'+relationship.table]){
        dataStore['loading'+relationship.table].then(
            function(){
                 // double check that the child class has been loaded, so instateRelationItems has something to instate.
                if (!dataStoreManager.checkCollection(relationship.childClass) && !parent[relationship.parentProperty]) {
                    var action = parent.api.fetchActionString('getAll', relationship.childClass);

                   // get data
                    parent.api.read(action).then(function (data) {
                        var instatedObjects = parent.inflator.instateAllObjectsFromJson(data);
                        // add returned data to cache
                        dataStoreManager.store(instatedObjects, true, relationship.childClass);
                        parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
                        dataStore['loading'+relationship.table] = null;
                    });
                } else {
                    var d = parent.$q.defer();

                    //the collection is present in the parent, but the objects have not been placed in the dataStore
                    if (parent[relationship.parentProperty]) {
                        dataStoreManager.store(parent.inflator.instateAllObjectsFromJson(parent[relationship.parentProperty]), relationship.className);
                    }
                    parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
                    d.resolve(relationship.parentProperty);
                    dataStore['loading' + relationship.table] = null;
                    return d.promise;
                }
            }
        )
    }
    // data not cached, get from the server
    else {
        var urlFragment = 'getRelationships&class1=' + parent.Class + '&class2=' + relationship.childClass;
        return parent.rootScope["loading"] = parent.rootScope[parent.Class + "sBusy"] = dataStore['loading' + relationship.table] = parent.api.read(urlFragment)
            .then(function (returnedPromise) {
                //cache result so we don't hit the server next time
                dataStoreManager.storeGerunds(returnedPromise.data, relationship.table);

                if (!dataStoreManager.checkCollection(relationship.childClass)) {
                    var action = parent.api.fetchActionString('getAll', relationship.childClass);

                    // get data
                    return parent.api.read(action).then(function (returned) {
                        var instatedObjects = parent.inflator.instateAllObjectsFromJson(returned.data);
                        // add returned data to cache
                        dataStoreManager.store(instatedObjects);
                        return parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
                    });
                } else {
                   var d = parent.$q.defer();
                   parent[relationship.parentProperty] = dataStoreManager.getManyToMany(parent, relationship);
                   d.resolve(relationship.parentProperty);
                   return d.promise;

                }

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
        var d = parent.$q.defer();
        parent[property] = dataStoreManager.getById(className, id);
        d.resolve(parent[property]);
        return d.promise;
    }
    else if (dataLoader.promiseMatrix[className] && dataLoader.promiseMatrix[className][id]) {
        return dataLoader.promiseMatrix[className][id].then(function (returnedPromise) {
            if (returnedPromise && returnedPromise.Class) {
                return parent[property] = returnedPromise.api ? returnedPromise : parent.inflator.instateAllObjectsFromJson(returnedPromise);
            }
        });
    }
    // not cached, get from server
    else {
        var getString = parent.api.fetchActionString("getById", className);
        var idParam = '&id=' + id;

        if (!dataLoader.promiseMatrix[className]) {
            dataLoader.promiseMatrix[className] = {};
        }

        return dataLoader.promiseMatrix[className][id] = parent.api.read(getString, idParam).then(function (returnedPromise) {
            if (returnedPromise && returnedPromise.data) {
                return parent[property] = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
            }
        });
    }
}

dataLoader.loadChildObjectByParentProperty = function (parent, property, className, int, childProperty, getString) {
    // check cache first
    if (dataStoreManager.checkCollection(className)) {
        var d = parent.$q.defer();
        parent[property] = dataStoreManager.getChildByParentProperty(className, childProperty, int);
        d.resolve(parent[property]);
        return d.promise;
    }
    // not cached, get from server
    else {
        var idParam = '&id=' + int;
        return parent.rootScope["loading"] = parent.api.read(getString, idParam).then(function (returnedPromise) {
            return parent[property] = parent.inflator.instateAllObjectsFromJson(returnedPromise.data);
        });
    }
}

dataLoader.recursivelyInstantiate = function(instatedObjects, parent){
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
