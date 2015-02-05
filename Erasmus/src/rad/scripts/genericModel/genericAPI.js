//api calls for app
'use strict';


angular
  .module('genericAPI',[])

	.factory('genericAPIFactory', function genericAPIFactory( $http, $q, $rootScope ) {

		var  api = {};

		api.baseUrl = '../../ajaxaction.php?action=';

		//Generic calls

		api.fetchActionString = function( actionName, className ) {
			var urlList = urlMapper.getList();

			if( !urlList[className] ) {
				console.log("ERROR: No class '" + className + "' found in url mappings!");
				return false;
			}
			if( !urlList[className][actionName] ) {
				console.log("ERROR: No action string for action '" + actionName + "' on class '" + className + "'!");
				return false;
			}

			return urlList[className][actionName];

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
				console.log(urlFragment);
				var url = api.buildRequestUrl( urlFragment, true, queryParam );

		    	var promise = $http.jsonp( url )
	            		.success( function( data ) {
							return data;
			            })
			            .error( function( data, status, headers, config ) {
			            	console.log('Error returned while reading data from the server.');
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

		api.save = function( object, urlFragment )
		{
				if( !urlFragment )var urlFragment = object.saveUrl;	
				var url = api.buildRequestUrl( urlFragment, false );

				var testPromise = $q.defer();
				testPromise.resolve(object)
				//return testPromise.promise;
				//test api post
				//url = 'http://angularormtest.apiary.io/hazards/1';

				var promise = $http.post(url, object)
					.success( function( data ){
						console.log(data);
						object = data;
						return object;
					})
					.error( function(){
						console.log('error')
					})
				
				return promise;

		}

		return api;
	});
