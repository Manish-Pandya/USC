//finds concepts in the concept bucket

'use strict';


/**********************************
**
**      DATA STORE
**
**********************************/

var dataStoreManager = {};

dataStoreManager.store = function( object, trusted, flavor )
{
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
                if(!dataStore[object[0].Class]){
                    dataStore[object[0].Class] = object;
                }
                //this collection already exists.  add only the unique indices to it
                else{
                    var i = object.length;
                    while(i--){
                        if(!dataStoreManager.getById(object[i].Class, object[i].Key_id)){
                            dataStore[object[0].Class][dataStore[object[0].Class].length] = object[i];
                        }else{
                            object[i] = dataStoreManager.getById(object[i].Class, object[i].Key_id);
                        }
                    }
                }
                dataStoreManager.mapCache(object[0].Class);

            }else{
                dataStoreManager.addToCollection( flavor );
                if(!dataStore[flavor]){
                    dataStore[flavor] = object;
                }else{
                    var i = object.length;
                    while(i--){
                        if(!dataStoreManager.getById(object[i].Class, object[i].Key_id)){
                            console.log(object[i].Class)
                            dataStore[object[0].Class][dataStore[object[0].Class].length] = object[i];
                        }else{
                            object[i] = dataStoreManager.getById(object[i].Class, object[i].Key_id);
                        }
                    }
                }
                dataStoreManager.mapCache(dataStore[flavor]);
                return object;
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
        dataStore[object.Class+'Copy'] =  $.extend(true,{},object);
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
                    if(whereClause){
                        console.log(whereClause)
                        var j = whereClause.length;
                        while(j--){
                            for(var prop in whereClause[j]){
                                //we check to see if the properties of the current object are null or not, based on the value of the currenty property of whereClause
                                if(whereClause[j][prop] == "NOT NULL"){
                                    //where clause's current property's value is "NOT NULL", so we only want this object from the cache if it's property isn't null
                                    if(!current[prop])getIt = false;
                                }else if(whereClause[j][prop] == "IS NULL"){
                                    //where clause's current property's value is "IS NULL", so we only want this object from the cache if it's property is null
                                    if(current[prop])getIt = false;
                                }else{
                                    //the object property is neither "NOT NULL" or "IS NULL"
                                    console.log(whereClause[j][prop]+' is neither "NOT NULL" or "IS NULL"');
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

dataStoreManager.mapCache = function( cacheClass )
{ 
    dataStore[cacheClass+'Map'] = [];
    var stuff = this.get(cacheClass);
    var length = stuff.length;
    var cachePosition = 0; 

    while(length--){
        var targetId = stuff[cachePosition].Key_id;
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
    if( !this.getById(object.Class, object.Key_id ) )dataStore[object.Class].push( object );
}

dataStoreManager.pushIntoCollection = function(object){
    if(dataStoreManager.getById(object.Class, object.Key_id))return;

    if(!dataStore[object.Class])dataStoreManager.store([object]);

    if(!dataStoreManager.getById(object.Class, object.Key_id)){
        dataStore[object.Class].push(object);
        if(!dataStore[object.Class+'Map'])dataStore[object.Class+'Map'] = [];
        dataStore[object.Class+'Map'][object.Key_id] = dataStore[object.Class+'Map'].length;
    }
}