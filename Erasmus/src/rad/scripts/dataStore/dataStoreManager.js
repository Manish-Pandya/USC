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
                dataStore[object.Class] = [object];
                dataStoreManager.mapCache(object.Class);
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
                dataStore[object[0].Class] = object;
                dataStoreManager.mapCache(object[0].Class);

            }else{
                dataStoreManager.addToCollection( flavor );
                dataStore[flavor] = object;
                dataStoreManager.mapCache(object[0].Class);
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
    console.log(dataStore[objectFlavor+'Map']);
    // get index of this room in the cache, no looping anymore!
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

dataStoreManager.getChildrenByParentProperty = function(collectionType, property, value)
{
        if(!dataStore[collectionType]){
            return [];
        }else{
            //store the lenght of the appropriate collection
            var i = dataStore[collectionType].length;
            var collectionToReturn = [];
            while(i--){
                var current = dataStore[collectionType][i];
                if(current[property] == value){
                    collectionToReturn.push( current );
                }
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