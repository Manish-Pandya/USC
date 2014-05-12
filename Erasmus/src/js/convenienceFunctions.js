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
          	 //console.log(failParam);
             onFail( obj, failParam );
         });
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
		updateObject: function( objDTO, obj, onSave, onFail, url, failParam, extra1, extra2, extra3){
		  //console.log(objDTO);
          return $http.post(  url, objDTO )
          .success( function( returnedObj ) {
          	console.log(returnedObj);
          	//console.log(obj);
            onSave(returnedObj, obj, extra1, extra2, extra3);
          })
          .error(function(data, status, headers, config, hazard){
          	 //console.log(failParam);
             onFail( obj, failParam );
         });
        },
        /**  
		* 	DELETE an object on server and in AngularJS $scope object
		*	
		*	@param (obj DTO)          A data transfer object.  Has the properties which will be updated
		*	@param (obj OBJ)          The object to be updated in the AngularJS $scope
		*   @param (function onSave)  AngularJS controller method to call if our server call returns a good code
		*	@param (function onFail)  AngularJS controller method to call if our server call returns a bad code
		*   @param (String url)       The URL on the server to which we post
		*   @param (Object failParam) Object to be passed to failure function
		*
		**/
		deleteObject: function( onSave, onFail, url,object,parent){
          return $http.delete(  url )
          .success( function( returnedObj ) {
          	//console.log(returnedObj);
            onSave(returnedObj,object,parent);
          })
          .error(function(data, status, headers, config, hazard){
             onFail(object,parent);
         });
        },

        /**  
		* 	Get data from the server via REST like call, call callback method of controller accordingly 
		*	
		*   @param (Function onSuccess)  AngularJS controller method to call if our server call returns a good code
		*	@param (Function onFail)     AngularJS controller method to call if our server call returns a bad code
		*   @param (String url)          The URL on the server to which we post
		*   @param (Object parentObject) An optional parent object.   If this is passed, we are doing an asynch query to load child data for a parent object, for example asychronously loading a hazard's SubHazards
		*
		**/

        getData: function( url, onSuccess, onFail, parentObject, adding ){
    	//use jsonp method of the angularjs $http object to request data from service layer
        	$http.jsonp(url)
            .success( function(data) {
               data.doneLoading = true;
               onSuccess(data,parentObject, adding);
            })
            .error(function(data, status, headers, config){
                onFail(data,parentObject);
            })
    	},
    	getDataFromPostRequest: function(url, data, onSuccess, onFail ){
			//console.log(data);
			$http.post(url,data)
            .success( function(data) {
               data.doneLoading = true;
               onSuccess(data);
            })
            .error(function(data, status, headers, config){
            	//console.log(status);
            	//console.log(headers());
            	//console.log(config);
            	//console.log(data);
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
	      if(!props) {var props = ["Key_id","Key_id"];}	     	
	      for (i=0;i<array.length;i++) {
			if (array[i][props[0]] === obj[props[1]]) {
				//console.log('true');
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
	          	 //console.log(failParam);
	             onFail( obj, failParam );
	         });
	  	},
	  	/**
		*
    	*	returns an array of strings, each one the Name of one of the user's role objects
    	*	@param (user, User)  user object
    	*
		**/
	  	getUserTypes: function( user ){
	  		if(user.Roles){
	  			rolesArray = [];
	  			for(i=0;user.Roles.length>i;i++){
	  				rolesArray.push(user.Roles[i].Name);
	  			}
	  			return rolesArray;
	  		}
	  		return false
	  	},
	  	/**
		*
    	*	Converts a UNIX timestamp to a Javascript date object
    	*	@param (time, int)  Unix timestamp to convert
    	*
		**/
		getUnixDate: function(time){
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
		/**
		*
    	*	Converts a MYSQL datetime to a Javascript date object
    	*	@param (time, string)  MYSQL datetime to convert
    	*
		**/
		getDate: function(time){
			Date.prototype.getMonthFormatted = function() {
			    var month = this.getMonth();
			    return month < 10 ? '0' + month : month; // ('' + month) for string result
			}

			// Split timestamp into [ Y, M, D, h, m, s ]
			var t = time.split(/[- :]/);

			// Apply each element to the Date function
			// create a new javascript Date object based on the timestamp
			var date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

			
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
			var formattedTime = {};
			formattedTime.formattedString = month + '/' + day + '/' + year;
			formattedTime.year = year;
			//console.log(formattedTime);
			return formattedTime;
		},
		setMysqlTime: function(date){
			var date;
			date = new Date();
			date = date.getUTCFullYear() + '-' +
			    ('00' + (date.getUTCMonth()+1)).slice(-2) + '-' +
			    ('00' + date.getUTCDate()).slice(-2) + ' ' + 
			    ('00' + date.getUTCHours()).slice(-2) + ':' + 
			    ('00' + date.getUTCMinutes()).slice(-2) + ':' + 
			    ('00' + date.getUTCSeconds()).slice(-2);
			//console.log(date);
			return date;
		},
		setIsDirty: function(obj){
			obj.IsDirty = !obj.IsDirty;
			return obj;
		},
		sendEmail: function(emailDto, onSendEmail, onFailSendEmail, url){
			//use jsonp method of the angularjs $http object to ask the server to send an email
			return $http.post(  url, emailDto )
			.success( function( returnedObj ) {
				//console.log(returnedObj);
				onSendEmail(returnedObj, emailDto);
			})
			.error(function(data, status, headers, config, hazard){
			 	onFailSendEmail();
			});
		}
	};
});
