//api calls for app
'use strict';


angular
  .module('genericAPI',[])

    .factory('genericAPIFactory', function genericAPIFactory( $http, $q, $rootScope ) {

        var  api = {};

        api.baseUrl = '../ajaxaction.php?action=';
        if(window.hasOwnProperty("radUpString"))api.baseUrl = '../ajaxaction.php?rad=true&action=';
        if(window.hasOwnProperty("upString"))api.baseUrl = upString + api.baseUrl;
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
            console.log(url);
            return $http.jsonp(url)
                    .then(function (response) {
                        if (typeof response.data == "undefined" || (response.data.Class && response.data.Class == "ActionError" && response.data.Message != "No rows returned")) {
                            api.userLoggedOut(response.data);
                        } else {
                            return response;
                        }
                    },
                    function (response) {
                        console.log(response);
                        api.userLoggedOut(response.data);
                    });

        }

        api.getRelatedItemsById = function( object, prop )
        {

                var entityMapLocation = object[prop + 'Relationship'];

                var urlFragment = entityMapLocation.queryString + '&id=' + object[entityMapLocation.keyReference];

                return api.read( urlFragment );
        }

        api.save = function( object, urlFragment, saveChildren )
        {
            console.log(object);
                //all the client-side classes have className properties.  When we instantiate one to save, we shouldn't need to manually set it's class.
                if(!object.Class && object.className)object.Class = object.className;
                if( !urlFragment )var urlFragment = api.fetchActionString( "save", object.Class );
                var url = api.buildRequestUrl( urlFragment, false );
                console.log(url);
                if(saveChildren)url = url + "&saveChildren=true";

                var promise = $http.post(url, object)
                    .then( function( data ){
                        object = data;
                        console.log(data);
                        return object;
                    },
                    function (response) {
                        api.userLoggedOut(response.data);
                    })
                    

                return promise;

        }

        api.userLoggedOut = function (data) {
            console.log(data);
            console.log(typeof data == "undefined");
            if (!data || (data.Class && data.Class == "ActionError" && data.Message != "No rows returned")) {
                console.log(location)
                window.location = "http://" + location.host + "/rsms";
                return true;
            }
            return false;
        }

        return api;
    });
