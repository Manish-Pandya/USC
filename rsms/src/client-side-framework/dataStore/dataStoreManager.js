//finds concepts in the concept bucket, put first it pours em in

'use strict';


/**********************************
**
**      DATA STORE
**
**********************************/

var dataStoreManager = {};
//this should make it into the refactor in a perhaps better form
dataStore.holderObject = {};
dataStoreManager.store = function( object, trusted, flavor )
{
        if(!object || object == null)return;
        if(!trusted)trusted = false;

        if( !(object instanceof Array) ){
            //we have a single object
            //add name of object or collection our list of collections in the cache
            if( !flavor ){
                dataStoreManager.addToCollection( object.Class, trusted );
                dataStoreManager.pushIntoCollection(object);
            }else{
                dataStoreManager.addToCollection( flavor );
                dataStore[flavor] = [object];
                dataStoreManager.mapCache(object.Class);
            }
        }else{
            //we have an array of objects
            if(!object.length){
                return [];
            }
            //add name of object or collection our list of collections in the cache
            if( !flavor ){
                dataStoreManager.addToCollection( object[0].Class, trusted );
                //this collection hasn't been created yet.  create it and add everything we've passed.
                if(!dataStore[object[0].Class]){
                    //console.log(object[0].Class);
                    dataStore[object[0].Class] = [];
                    dataStore[object[0].Class] = dataStore[object[0].Class].concat(object);
                    dataStoreManager.mapCache(object[0].Class);
                }
                //this collection already exists.  add only the unique indices to it.
                else{
                    var i = object.length;
                    while(i--){
                        if(!dataStoreManager.getById(object[i].Class, object[i].Key_id)){
                            dataStoreManager.pushIntoCollection(object[i]);
                        }
                        object[i] = dataStoreManager.getById(object[i].Class, object[i].Key_id);
                    }
                }
            }else{
                dataStoreManager.addToCollection( flavor );
                if(!dataStore[flavor]){
                    dataStore[flavor] = object;
                }else{
                    var i = object.length;
                    while(i--){
                        if(!dataStoreManager.getById(object[i].Class, object[i].Key_id)){
                            dataStore[object[0].Class][dataStore[object[0].Class].length] = object[i];
                        }else{
                            object[i] = dataStoreManager.getById(object[i].Class, object[i].Key_id);
                        }
                    }
                }
                dataStoreManager.mapCache(dataStore[flavor]);
            }
        }
}

dataStoreManager.addToCollection = function( type, trusted )
{
        //if we don't have the name of this type of object or a name for this array of objects, push it into the collection
        if( !dataStore.Collections.hasOwnProperty( type ) || !dataStore.Collections[type].trusted ){
            dataStore.Collections[type] = {type:type, trusted:trusted};
        }
}

dataStoreManager.removeFromCollection = function( type )
{
        delete dataStore.Collections[type];
}

dataStoreManager.checkCollection = function( type )
{

        if( dataStore.Collections.hasOwnProperty( type ) ) return true;
        return false;
}

dataStoreManager.purge = function( objectFlavor )
{
        delete dataStore[objectFlavor];
}

dataStoreManager.get = function( objectFlavor )
{
        return dataStore[objectFlavor];
}

dataStoreManager.getById = function( objectFlavor, key_id )
{
    // get index of this room in the cache, no looping anymore!
    if(!dataStore[objectFlavor+'Map'] || typeof dataStore[objectFlavor+'Map'][key_id] === 'undefined')return false;
    var location = dataStore[objectFlavor+'Map'][key_id];
    return dataStore[objectFlavor][location];
}

dataStoreManager.getIfExists = function( objectFlavor, key_id )
{
        if( !key_id ) {
            if( dataStore.Collections.hasOwnProperty( objectFlavor ) )return true;
            return false;
        }else{
            if( dataStore.Collections.hasOwnProperty( objectFlavor ) && dataStore.Collections[objectFlavor].Key_id == key_id)return true;
            return false;
        }
        return false;
}

dataStoreManager.setIsDirty = function( object )
{
        dataStore[object.Class].setIsDirty( !dataStore[object.Class].IsDirty );
}

dataStoreManager.createCopy = function( object )
{
        dataStore[object.Class+'Copy'] =  $.extend(null,{},object);
        return dataStore[object.Class+'Copy'];
}

dataStoreManager.replaceWithCopy = function( object )
{
        //replace the object with the cached copy version
        for( var prop in object ){
            object[prop] = dataStore[object.Class+'Copy'][prop];
        }
        //clear the copy from the cache
        dataStore.purge( object );
}

dataStoreManager.setEditStates = function( object )
{
        //set the other objects in this one's collection to the non-edit state
        if(dataStore[object.Class]){
            var len = dataStore[object.Class].length

            for(var i=0; i<len; i++ ){
                dataStore[object.Class][i].Edit = 'false';
            }
        }

        object.Edit = true;

}

dataStoreManager.deleteCopy = function( object )
{
        dataStoreFactory.purge( window[object.Class+"Copy"] );
}

dataStoreManager.getChildrenByParentProperty = function(collectionType, property, value, whereClause)
{
        if(!dataStore[collectionType]){
            return [];
        }else{

            //store the lenght of the appropriate collection
            var i = dataStore[collectionType].length;
            var collectionToReturn = [];
            while(i--){
                var getIt = false;
                var current = dataStore[collectionType][i];
                if(current[property] == value){
                    getIt = true;

                    //do we have a whereClause in our "query"?
                    // whereClause looks like this: [{propertyToEvaluate: 'valueToBeCompared'}]
                    if(whereClause){
                        var j = whereClause.length;
                        while(j--){
                            for(var prop in whereClause[j]){
                                //we check to see if the properties of the current object are null or not, based on the value of the currenty property of whereClause
                                if(whereClause[j][prop] == "NOT NULL"){
                                    //where clause's current property's value is "NOT NULL", so we only want this object from the cache if it's property isn't null
                                    if (!current[prop]) getIt = false;
                                }else if(whereClause[j][prop] == "IS NULL"){
                                    //where clause's current property's value is "IS NULL", so we only want this object from the cache if it's property is null
                                    if(current[prop])getIt = false;
                                }else{
                                    //the object property is neither "NOT NULL" or "IS NULL"
                                    if(current[prop] != whereClause[j][prop]) getIt = false;
                                }
                                /*
                                //whereClause[j][prop] is neither "NOT NULL" or "IS NULL", so compare
                                else if(current[prop] != whereClause[j][prop]){
                                    getIt = false;
                                    break;
                                }
                                */
                            }
                        }
                    }
                }

                if(getIt)collectionToReturn.push( current );
            }
            return collectionToReturn;

        }

}

dataStoreManager.getRelatedItems = function( type, relationship, key, foreign_key )
{
        if(!dataStore[type]){
            return [];
        }else{
            //store the length of the appropriate collection
            var i = dataStore[type].length;
            var collectionToReturn = [];
            while(i--){
                var j = relationship.length
                    var current = dataStore[type][i];
                while(j--){
                    if(current[foreign_key] == relationship[j][key]){
                        collectionToReturn.push(current);
                    }
                }

            }

            return collectionToReturn;

        }
}

/******
**gets a single child object from the cache
**@param String type  the type of object we are looking for in the cache
**@param String prop  the property of the object to compare (i.e. key_id, parent_id, etc.)
**@param int key
**/
dataStoreManager.getChildByParentProperty = function(type, prop, key){
    if(!dataStore[type])return null;
    var i = dataStore[type].length;
    while(i--){
        if(dataStore[type][i][prop] == key)return dataStore[type][i];
    }
    return null;
}

dataStoreManager.mapCache = function( cacheClass )
{
    if(!cacheClass)return;
    dataStore[cacheClass+'Map'] = [];
    var stuff = this.get(cacheClass);
    var length = stuff.length;
    var cachePosition = 0;

    if(stuff[0].ID_prop){
        var ID_prop = stuff[0].ID_prop;
    }else{
        var ID_prop = "Key_id";
    }

    while(length--){
        var targetId = stuff[cachePosition][ID_prop];
        dataStore[cacheClass+'Map'][targetId] = cachePosition;
        cachePosition++;
    }

}

dataStoreManager.setModalData = function(data)
{
    if(!dataStore.modalData)dataStore.modalData={};
    for(var prop in data){
        dataStore.modalData[prop] = data[prop];
        dataStore.modalData[prop+'Copy'] = dataStoreManager.createCopy(data[prop]);
    }
}

dataStoreManager.getModalData = function()
{
    return dataStore.modalData;
}

dataStoreManager.addOnSave = function( object )
{
    if( !this.getById(object.Class, object.Key_id ) ){
        if(!dataStore[object.Class])dataStore[object.Class]=[];
        dataStore[object.Class].push( object );
    }
}

dataStoreManager.pushIntoCollection = function(object){

    if(object.ID_prop){
        var ID_prop = object.ID_prop;
    }else{
        var ID_prop = "Key_id";
    }

    if(dataStoreManager.getById(object.Class, object[ID_prop]))return;

    if(!dataStore[object.Class])dataStoreManager.store([object]);

    if(!dataStoreManager.getById(object.Class, object[ID_prop])){
        dataStore[object.Class].push(object);
        if(!dataStore[object.Class+'Map'])dataStore[object.Class+'Map'] = [];
        dataStore[object.Class+'Map'][object.Key_id] = dataStore[object.Class].length-1;
    }
}

/**
*
*   MNAGEMENT OF MANY TO MANY RELATIONSHIPS THROUGH STORAGE OF ARRAYS OF DOUPLES
*
*/
dataStoreManager.storeGerunds = function(collection, tableName){
    if(!tableName && collection[0].table)var tableName = collection[0].table;
    if(!tableName)return;
    dataStore[tableName] = collection;
}

dataStoreManager.addGerund = function(gerundObject, tableName){
    if(!tableName && gerundObject.table)var tableName = gerundObject.table;
    if(!tableName)return;
    dataStore[tableName].push(gerundObject);
}
//todo
//given two objects, remove the relationship between them

dataStoreManager.removeGerund = function(obj1, obj2){

}
/**
 * @param Various parent       parent object we want to get child objects for
 * @param Object   relationShip object mapping the relationship between our parent object and the child objects we want to retrieve
 * @return Array matches
 */
dataStoreManager.getManyToMany = function(parent, relationship){
    if(!dataStore[relationship.childClass] || !dataStore[relationship.table])return false;
    //if the parent property is not set, or not an array, initialize one
    if(!parent[relationship.parentProperty] || parent[relationship.parentProperty].constructor !== Array)parent[relationship.parentProperty] = [];

    var matches = [];
    var i = dataStore[relationship.table].length;
    while(i--){
        if(relationship.isMaster){
            //master_id will be parent's key_id
            if(dataStore[relationship.table][i].ParentId == parent.Key_id){
                 matches.push(dataStoreManager.getById(relationship.childClass, dataStore[relationship.table][i].ChildId))
            }
        }else{
            //master_id will be relationship.childClass' key_id
            if(dataStore[relationship.table][i].ChildId){
                matches.push(dataStoreManager.getById(relationship.childClass, dataStore[relationship.table][i].ParentId))
            }
        }
    }
    return matches;
}

