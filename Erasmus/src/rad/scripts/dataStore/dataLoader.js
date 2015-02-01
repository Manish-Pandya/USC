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
    This class does not return anything - instead, it sets the given property on parent.
    This avoids any asynchronous vs synchronous nastiness.
*/
dataLoader.loadChildObject = function( parent, property, relationship )
{
        // check cache first
        if( dataStoreManager.checkCollection(relationship.className) ) {
            parent[property] = dataStoreManager.getChildrenByParentProperty(
                relationship.className,
                relationship.keyReference,
                parent[relationship.paramValue]
                );
        }
        // if not in cache, get from server
        else {

            // prepare url to send (TODO shoudl API handle this?)
            var urlFragment = relationship.methodString;
            var paramValue = '&' + relationship.paramName + '=' + parent[relationship.paramValue];

            parent.rootScope[parent.Class+"sBusy"] = parent.api.read( urlFragment, paramValue )
                .then(function( returnedPromise ) {
                    parent[property] = parent.inflator.instateAllObjectsFromJson( returnedPromise.data );
                });
        }
}
