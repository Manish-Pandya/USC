angular.module('convenienceMethodModule', ['ngRoute'])
.factory('convenienceMethods', function($http){
	return{
		//
		/**  
		* 	loop through an object, set its properties to match the DTO
		*	
		*	@param (Object dto)     A data transfer object.  Has the properties which will be updated
		*	@param (Object obj)     The object to be updated in the AngularJS $scope
		*
		**/
		setPropertiesFromDTO: function(dto,obj){
	        for (var key in dto) {
	          if (dto.hasOwnProperty(key)) {
	            obj[key] = dto[key];
	          }
	        }
		},
		/**  
		* 	UPDATE an object on server and in AngularJS $scope object
		*	
		*	@param (obj DTO)          A data transfer object.  Has the properties which will be updated
		*	@param (obj OBJ)          The object to be updated in the AngularJS $scope
		*   @param (function onSave)  AngularJS controller method to call if our server call returns a good code
		*	@param (function onFail)  AngularJS controller method to call if our server call returns a bad code
		*   @param (String url)       The URL on the server to which we post
		*   @param (Object failParam) Object to be passed to failure function
		*
		**/
		saveNewObject: function( obj, onSave, onFail, url, failParam ){
          return $http.post(  url, obj )
          .success( function( returnedObj ) {
                onSave(returnedObj, obj );
          })
          .error(function(data, status, headers, config, hazard){
          	 console.log(failParam);
             onFail( obj, failParam );
         });
        },/**  
		* 	UPDATE an object on server and in AngularJS $scope object
		*	
		*	@param (obj DTO)          A data transfer object.  Has the properties which will be updated
		*	@param (obj OBJ)          The object to be updated in the AngularJS $scope
		*   @param (function onSave)  AngularJS controller method to call if our server call returns a good code
		*	@param (function onFail)  AngularJS controller method to call if our server call returns a bad code
		*   @param (String url)       The URL on the server to which we post
		*   @param (Object failParam) Object to be passed to failure function
		*
		**/
		updateObject: function( objDTO, obj, onSave, onFail, url, failParam, haz, room, parent){
          return $http.post(  url, objDTO )
          .success( function( returnedObj ) {
          	console.log(returnedObj);
            onSave(returnedObj, obj, haz, room, parent);
          })
          .error(function(data, status, headers, config, hazard){
          	 console.log(failParam);
             onFail( obj, failParam );
         });
        },

        /**  
		* 	Get data from the server via REST like call, call callback method of controller accordingly 
		*	
		*   @param (Function onSuccess)  AngularJS controller method to call if our server call returns a good code
		*	@param (Function onFail)     AngularJS controller method to call if our server call returns a bad code
		*   @param (String url)          The URL on the server to which we post
		*
		**/

        getData: function( url, onSuccess, onFail ){
    	//use jsonp method of the angularjs $http object to request data from service layer
        	$http.jsonp(url)
            .success( function(data) {
               data.doneLoading = true;
               onSuccess(data);
            })
            .error(function(data, status, headers, config){
                onFail(data);
            })
    	},
    	getDataFromPostRequest: function(url, data, onSuccess, onFail ){
			console.log(data);
			$http.post(url,data)
            .success( function(data) {
               data.doneLoading = true;
               onSuccess(data);
            })
            .error(function(data, status, headers, config){
            	console.log(status);
            	console.log(headers());
            	console.log(config);
            	console.log(data);
                onFail(data);
            })
    	},
    	setData: function(data){
    		data = data;
    	},
    	/**
    	*
    	*	Boolean to test if an object or property exists, and if so, if it has length
    	*	@param (Object obj)
    	*
    	**/
    	getHasLength: function(obj){
    		if(obj){
			    if(obj !== null){
			      if(obj.length > 0){
			        return true
			      }
			    }
			    return false;
			}
		    return false;
		},
		/**
		*
    	*	Boolean returns true if an array contains an object
    	*	@param (Array, obj)  array to search for object
    	*	@param (Object obj)  object to find in array
		*	@param (Array, props)	OPTIONAL THIRD PARAMETER -- an array of properties to evaluate if we are not using key ids -- index one should be the property searched in the array, index two the property of the object seached for in the array
		*		(ie [Key_id, Reponse_id] will evaluate the objects Response_id property against the Key_id proptery of each object in the array)
		*
    	*	@param (Bool, returnIdx)	OPTIONAL FOURTH PARAM Setting to true will cause this method to return the index of the object in an array instead of a boolean true, if the array contains the object
    	*
		**/
		arrayContainsObject: function(array, obj, props, returnIdx) {
		  console.log(array);
		  console.log(obj);
	      if(!props) {var props = ["Key_id","Key_id"];}	     	
	      for (i=0;i<array.length;i++) {
			if (array[i][props[0]] === obj[props[1]]) {
				console.log('true');
				if(returnIdx) return i;
				return true;
			}   
	      }
	      return false;
	  	},
		/**
		*
    	*	Set the relationship between two objects
    	*	@param (Object, object1)  first object
    	*	@param (Object object2)   second object
    	*
		**/
	  	setObjectRelationship: function( object1, object2, onSuccess, onFail, url, failParam ){
	  		objDTO = {};
	  		objDTO.object1 = object1;
	  		objDTO.object1 = object2;
	  		return $http.post(  url, objDTO )
	          .success( function( returnedObj ) {
	                onSuccess(returnedObj, obj );
	          })
	          .error(function(data, status, headers, config, hazard){
	          	 console.log(failParam);
	             onFail( obj, failParam );
	         });
	  	},
	  	/**
		*
    	*	Converts a UNIX timestamp to a Javascript date object
    	*	@param (time, int)  Unix timestamp to convert
    	*
		**/
		getDate: function(time){
			Date.prototype.getMonthFormatted = function() {
			    var month = this.getMonth();
			    return month < 10 ? '0' + month : month; // ('' + month) for string result
			}
			// create a new javascript Date object based on the timestamp
			// multiplied by 1000 so that the argument is in milliseconds, not seconds
			var date = new Date(time*1000);
			// hours part from the timestamp
			var hours = date.getHours();
			// minutes part from the timestamp
			var minutes = date.getMinutes();
			// seconds part from the timestamp
			var seconds = date.getSeconds();

			var month = date.getMonth()+1;
			var day = date.getDate();
			var year = date.getFullYear();

			// will display date in mm/dd/yy format
			var formattedTime = month + '/' + day + '/' + year;

			return formattedTime;
		},
		setIsDirty: function(obj){
			obj.IsDirty = !obj.IsDirty;
			return obj;
		}
	};
});
