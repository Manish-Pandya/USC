//api calls for app
'use strict';


angular
  .module('genericAPI',[])

    .factory('genericAPIFactory', function genericAPIFactory( $http, $q, $rootScope ) {

        var  api = {};

        api.baseUrl = '../ajaxaction.php?action=';
        if(radUpString)api.baseUrl = '../ajaxaction.php?rad=true&action=';
        if(upString)api.baseUrl = upString + api.baseUrl;
        //Generic calls

        api.fetchActionString = function( actionName, className, queryParam ) {
            var urlList = urlMapper.list;

            if( !urlList[className] ) {
                console.log("ERROR: No class '" + className + "' found in url mappings!");
                return false;
            }
            if( !urlList[className][actionName] ) {
                console.log("ERROR: No action string for action '" + actionName + "' on class '" + className + "'!");
                return false;
            }
            var actionString = urlList[className][actionName];
            if(queryParam)actionString = actionString+'&'+queryParam+'=true';
            return actionString;

        }

        api.buildRequestUrl = function( urlFragment, callback, queryParam )
        {

                var url = api.baseUrl + urlFragment;
                if(queryParam) url = url + queryParam;
                if( callback ) url = url + '&callback=JSON_CALLBACK';
                return url;

        }

        api.read = function( urlFragment, queryParam )
        {
                var url = api.buildRequestUrl( urlFragment, true, queryParam );

                var promise = $http.jsonp( url )
                        .success( function( data ) {
                            return data;
                        })
                        .error( function( data, status, headers, config ) {
                            console.log('Error returned while reading data from the server.');
                            console.log(data);
                            //todo
                            //figure out how we will handle errors going forward.
                            //probably, we will simply need to return an error object
                            //in theory, rejecting the promise and passing in the response data should handle this
                            return status;
                        });

                return promise;
        }

        api.getRelatedItemsById = function( object, prop )
        {

                var entityMapLocation = object[prop + 'Relationship'];

                var urlFragment = entityMapLocation.queryString + '&id=' + object[entityMapLocation.keyReference];

                return api.read( urlFragment );
        }

        api.save = function( object, urlFragment, saveChildren )
        {
                //all the client-side classes have className properties.  When we instantiate one to save, we shouldn't need to manually set it's class.
                if(!object.Class && object.className)object.Class = object.className;
                if( !urlFragment )var urlFragment = api.fetchActionString( "save", object.Class );
                var url = api.buildRequestUrl( urlFragment, false );
                console.log(url);
                if(saveChildren)url = url + "&saveChildren=true";

                console.log(object);
                var promise = $http.post(url, object)
                    .success( function( data ){
                        object = data;
                        return object;
                    })
                    .error( function(){
                        return false;
                        console.log('error while saving object: ' + object);
                    })

                return promise;

        }

        return api;
    });
