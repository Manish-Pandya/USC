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
        
        if( !( object instanceof Array ) ){
            //we have a single object
            //add name of object or collection our list of collections in the cache
            if( !flavor ){
                dataStoreManager.addToCollection( object.Class, trusted );
                dataStore[object.Class] = object;
            }else{
                dataStoreManager.addToCollection( flavor );
                dataStore[flavor] = object;
            }
        }else{
            //we have an array of objects
            //add name of object or collection our list of collections in the cache
            if( !flavor ){
                dataStoreManager.addToCollection( object[0].Class+'s', trusted );
                dataStore[object[0].Class+'s'] = object;
            }else{
                dataStoreManager.addToCollection( flavor );
                dataStore[flavor] = object;
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
        //if we don't have this object in the cache, but we DO have a collection of all such objects, we don't need to make an api call
        if( dataStore.Collections.hasOwnProperty( type+'s' ) ) return true;
        return false;
}

dataStoreManager.purge = function( objectFlavor )
{
        delete dataStore[objectFlavor];
}

dataStoreManager.get = function( objectFlavor )
{

        if(dataStore[objectFlavor]){
            //we have the object or collection cached, so resolve the promise with it and we're done
            return dataStore[objectFlavor];
        }else{
            return 'Not Found';
        }

}

dataStoreManager.getById = function( objectFlavor, key_id )
{
    //we are looking for a single object.  we don't have that object cached, but we DO have a collection of all such objects
    //find the object in the collection
    var array = dataStore[objectFlavor+'s'];
    var len = array.length;

    for(var i =0; i<len; i++){
        var current = array[i];
        if( current.getKey_id() == key_id ){
             //set the object in the cache, then resolve the promise with it
             dataStoreManager.store( current );
             return current;
        }
    }
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
        dataStore[object.Class+'Copy'] = new window[object.Class];

        for( var prop in object ){
            dataStore[object.Class+'Copy'][prop] = object[prop];
        }
}

dataStoreManager.replaceWithCopy = function( object )
{      
        console.log(dataStore[object.Class+'Copy']);
        //replace the object with the cached copy version
        for( var prop in object ){
            object[prop] = dataStore[object.Class+'Copy'][prop];
        }
        console.log(object);
        //clear the copy from the cache
        dataStore.purge( object );
}

dataStoreManager.setEditStates = function( object )
{
        //set the other objects in this one's collection to the non-edit state
        if(dataStore[object.Class+'s']){
            var len = dataStore[object.Class+'s'].length

            for(var i=0; i<len; i++ ){
                dataStore[object.Class+'s'][i].Edit = 'false';
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
        console.log()

        if(!dataStore[collectionType+'s']){
            return 'Not found';
        }else{
                            console.log(dataStore[collectionType]);

            //store the lenght of the appropriate collection
            var i = dataStore[collectionType+'s'].length;
            var collectionToReturn = [];

            while(i--){
                var current = dataStore[collectionType+'s'][i];

                if(current[property] == value){
                    collectionToReturn.push( current );
                }
            }

            return collectionToReturn;

        }

}