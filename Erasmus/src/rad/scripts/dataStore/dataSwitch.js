'use strict';

/**
 *  This class simplifies data accessing, handling checking the Cache first for data, then contacting
 * the API if that item is not present in the cache.
 *
 * @author Perry
 */

// constructor
var dataSwitch = {};

dataSwitch.getChildObject = function( parent, property, relationship )
{


        // check cache first
        if( dataStoreManager.checkCollection(relationship.className) ) {
            parent[property] = dataStoreManager.getChildrenByParentProperty(
                relationship.className,
                relationship.keyReference,
                parent[queryParam]
                );
        }
        // if not in cache, get from server
        else {

            // prepare url to send (TODO shoudl API handle this?)
            var urlFragment = relationship.queryString;
            var queryParam = '&' + relationship.paramName + '=' + parent[relationship.queryParam];

            parent.rootScope[parent.Class+"sBusy"] = parent.api.read( urlFragment, queryParam )
                .then(function( returnedPromise ) {
                    parent[property] = parent.inflator.instateAllObjectsFromJson( returnedPromise.data );
                });
        }
}
