//a bucket of concepts

'use strict';


/**********************************
**
**		DATA STORE
**
**********************************/

var dataStore = {};

dataStore.Collections = {};

dataStore.store = function( object )
{
		dataStore[object.Class] = object;
}

dataStore.purge = function( objectFlavor )
{
		delete dataStore[objectFlavor]
}

dataStore.get = function( objectFlavor )
{
		return dataStore[objectFlavor];
}
        	
