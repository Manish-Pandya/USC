angular.module('convenienceMethodWithRoleBasedModule', ['ngRoute', 'roleBased', 'ui.select', 'ngSanitize'])
.config(['$provide', function ($provide) {
    $provide.decorator('orderByFilter', ['$delegate', '$parse', function ($delegate, $parse) {
        return function () {
            var predicates = arguments[1];
            var invertEmpties = arguments[3];
            if (angular.isDefined(invertEmpties)) {
                if (!angular.isArray(predicates)) {
                    predicates = [predicates];
                }
                var newPredicates = [];
                angular.forEach(predicates, function (predicate) {
                    if (angular.isString(predicate)) {
                        var trimmed = predicate;
                        if (trimmed.charAt(0) == '-') {
                            trimmed = trimmed.slice(1);
                        }
                        var keyFn = $parse(trimmed);
                        newPredicates.push(function (item) {
                            var value = keyFn(item);
                            return (angular.isDefined(value) && value != null) == invertEmpties;
                        })
                    }
                    newPredicates.push(predicate);
                });
                predicates = newPredicates;
            }
            return $delegate(arguments[0], predicates, arguments[2]);
        }
    }])
}])
.run(function($rootScope) {
    $rootScope.Constants = Constants;
})
.factory('convenienceMethods', function ($http, $q, $rootScope, $modal, naturalService) {
    var methods =  {
        
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
                methods.userLoggedOut(data);
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
                if(returnedObj.IsError) {
                    onFail(returnedObj);
                } else {
                    onSave(returnedObj, obj, extra1, extra2, extra3);
                    methods.userLoggedOut(returnedObj);
                }
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
        deleteObject: function( onSave, onFail, url, object, parent, parent2){
            return $http.delete(  url )
            .success( function( returnedObj ) {
                //console.log(returnedObj);
                onSave(returnedObj, object, parent, parent2);
            })
            .error(function (data, status, headers, config, hazard) {
                methods.userLoggedOut(data);
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
            return $http.jsonp(url)
                .success( function(data) {
                    data.doneLoading = true;
                    onSuccess(data, parentObject, adding);
                })
                .error(function (data, status, headers, config) {
                    methods.userLoggedOut(data);
                    onFail(data,parentObject);
                })
        },

        /**
        * 	Get data from the server via REST like call, call callback method of controller accordingly
        *
        *	@param (Function onFail)     method to call if our server call returns a bad code
        *   @param (String url)          The URL on the server to which we post
        *
        **/
        getDataAsPromise: function( url, errorCallback ){
            //use jsonp method of the angularjs $http object to request data from service layer
            var promise = $http.jsonp(url)
                .success(function (data) {
                    console.log(data);
                    data.doneLoading = true;
                    return data;
                })
                .error(function (data, status, headers, config) {
                    console.log(data);
                    methods.userLoggedOut(data);
                    errorCallback();
                });
            return promise;
        },
        getDataAsDeferredPromise: function( url ){
            var deferred = $q.defer();
            //use jsonp method of the angularjs $http object to request data from service layer
            $http.jsonp(url)
                .success(function (data) {
                    console.log(data);
                    deferred.resolve(data);
                })
                .error(function (data, status, headers, config) {
                    console.log(data);

                    methods.userLoggedOut(data);
                    deferred.reject(data);
                });
            return deferred.promise;
        },
        saveDataAndDefer: function(url, obj){
            var deferred = $q.defer();
            var promise = $http.post(url,obj)
            .success( function(data) {
                data.doneLoading = true;
                deferred.resolve(data);
            })
                .error(function(data, status){
                    methods.userLoggedOut(data);
                    deferred.reject(data);
                });
            return deferred.promise;
        },
        getDataFromPostRequest: function(url, data, onSuccess, onFail ){
            //console.log(data);
            $http.post(url,data)
            .success( function(data) {
                data.doneLoading = true;
                onSuccess(data);
            })
            .error(function (data, status, headers, config) {
                methods.userLoggedOut(data);
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
        arrayContainsObject: function (array, obj, props, returnIdx) {
            if (!props) { var props = ["Key_id", "Key_id"]; }
            if (!array || !array.length) return false;
            for (var localI=0;localI<array.length;localI++) {
                if (array[localI][props[0]] === obj[props[1]]) {
                    if(returnIdx)return localI;
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
                methods.userLoggedOut(data);
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
        getDateString: function(time){

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

        /**
         * Returns true if the given date falls before isBeforeDate
         */
        dateIsBefore: function(date, isBeforeDate){
            var _date = date;
            var _before = isBeforeDate;

            if( !(_date instanceof Date)){
                _date = new Date(Date.parse(_date));
            }

            if( !(_before instanceof Date)){
                _before = new Date(Date.parse(_before));
            }

            return _date.getTime() < _before.getTime();
        },

        /*
        *
        *	Converts a Javascript date object to a MYSQL datetime formatted string
        *	@param (date, Date)  JS Date to convert
        */
        setMysqlTime: function (date) {
            if (!date && date !== false) return null;
            

            if (!date) var date = new Date();

            if (!(date instanceof Date)) date = new Date(Date.parse(date));

            //handle the fact that Microsoft's fancy new Edge browser falls victim to Y2K like bug in 2017, when this code was written.
            if (date.getFullYear() < 1970) {
                date.setFullYear(date.getFullYear() + 100);
            }

            date = date.getFullYear() + '-' +
                ('00' + (date.getMonth()+1)).slice(-2) + '-' +
                ('00' + date.getDate()).slice(-2) + ' ' +
                ('00' + date.getHours()).slice(-2) + ':' +
                ('00' + date.getMinutes()).slice(-2) + ':' +
                ('00' + date.getSeconds()).slice(-2);
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
        },
        watchersContainedIn: function(scope) {
            var watchers = (scope.$$watchers) ? scope.$$watchers.length : 0;
            var child = scope.$$childHead;
            while (child) {
                watchers += (child.$$watchers) ? child.$$watchers.length : 0;
                child = child.$$nextSibling;
            }
            return watchers;
        },

        //copy an object, not by reference
        copyObject: function(obj) {
            //var newObject = JSON.parse(JSON.stringify(obj));
            if (obj instanceof Array) {
                var array = [];
                var i = obj.length;
                while (i--) {
                    array.unshift($.extend(null, {}, obj[i]))
                }
                return array;
            } else {
                return $.extend(null, {}, obj);
            }
        },

        getDate: function (mysql_string){
            var t, result = null;
            if (typeof mysql_string === 'string') {
                // Delimit date/time parts with anything non-numeric
                t = mysql_string.split(/\D/);
                if (~mysql_string.indexOf("-")) {
                    //when t[3], t[4] and t[5] are missing they defaults to zero
                    result = new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
                } else if (~mysql_string.indexOf("/")) {
                    if (t[2] && t[2].length == 2) t[2] = '20' + t[2];
                    console.log("T IS", t);
                    result = new Date(t[2], t[1]-1, t[0]);
                    console.log(result.toLocaleDateString())
                }
            }

            return result;  
        },

        userLoggedOut: function (data) {
            if (data && data.Class && data.Class == "ActionError") {
                $rootScope.requestError = data.Message;
            }
        },
        dateToIso: function (input, object, propertyName, setToString, nullable) {
            if (!input && !nullable) {
                return "N/A";
            } else if (!input && nullable) {
                return null;
            }

            // Split timestamp into [ Y, M, D, h, m, s ]
            var t = input.split(/[- :]/);
            // Apply each element to the Date function
            var d = new Date(t[0], t[1] - 1, t[2]);

            //at times like these, it's important to consider the nature of addition, concatonation and the universe in general.
            input = d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear();
            if (object && propertyName) {
                if (!setToString) {
                    object["view_" + propertyName] = d;
                } else {
                    object["view_" + propertyName] = input;
                }
            }
            if (t[0] == "0000" && !nullable) return "N/A";
            return input
        },
        sortAlphaNum: function (field) {
            //console.log(property);
            //if (!property) property = "Name";
            if (!field) return;
            return function (item) {
                var val = field.indexOf(".") != -1 ? _.get(item, field) : item[field];
                return naturalService.naturalValue(val);
            }
        },
        checkHazards: function (room, pis) {
            urlSegment = "../../ajaxaction.php?action=getRoomHasHazards&id="+room.Key_id;
            var piIds = pis.map(function (pi) { return pi.Key_id });
            urlSegment += "&" + $.param({ piIds: piIds });
            return $http.get(urlSegment).then(function (r) { return r.data; });
        },

        /**
         * Opens a generic app-styled confirmation dialog
         * @param {String} title - Dialog title (defaults to 'Confirmation')
         * @param {String} message - Dialog message
         * @param {String} confirm_text - Confirm button text (defaults to 'Continue')
         * @param {String} cancel_text  - Reject button text (defaults to 'Cancel')
         */
        modalConfirm: function (title, message, confirm_text, cancel_text){
            let instance = $modal.open({
                resolve: {
                    title: function(){return title; },
                    message: function(){return message; },
                    confirm_text: function(){return confirm_text; },
                    cancel_text: function(){return cancel_text; }
                },
                controller: function($scope, $modalInstance, title, message, confirm_text, cancel_text){
                    $scope.title = title;
                    $scope.message = message;
                    $scope.confirm_text = confirm_text;
                    $scope.cancel_text = cancel_text;
                    $scope.cancel = () => $modalInstance.dismiss(),
                    $scope.confirm = () => $modalInstance.close()
                },
                template: `<div>
                            <div class="modal-header theme-main-element" style="padding:0;">
                                <h2 style="padding:5px">{{title || 'Confirmation'}}</h2>
                            </div>

                            <div class="modal-body" ng-if="message">
                                <p ng-bind-html="message"></p>
                            </div>

                            <div class="modal-footer">
                                <a class="btn btn-large btn-success left" ng-click="confirm()"><i class="icon-checkmark"></i>{{confirm_text || 'Continue'}}</a>
                                <a class="btn btn-large btn-danger left" ng-click="cancel()"><i class="icon-cancel-2"></i>{{cancel_text || 'Cancel'}}</a>
                            </div>
                        </div>`
            });

            // Return the modal instances promise
            //    Dismiss => reject
            //    Close   => resolve
            return instance.result;
        }
    }
    return methods;
})
    // The core natural service
.factory("naturalService", ["$locale", function ($locale) {
    // the cache prevents re-creating the values every time, at the expense of
    // storing the results forever. Not recommended for highly changing data
    // on long-term applications.
    var natCache = {},
		// amount of extra zeros to padd for sorting
        padding = function (value) {
            return '00000000000000000000'.slice(value.length);
        },

		// Calculate the default out-of-order date format (d/m/yyyy vs m/d/yyyy)
        natDateMonthFirst = $locale.DATETIME_FORMATS.shortDate.charAt(0) == 'm';
    // Replaces all suspected dates with a standardized yyyy-m-d, which is fixed below
    fixDates = function (value) {
        if (!value) return false;
        // first look for dd?-dd?-dddd, where "-" can be one of "-", "/", or "."
        return value.replace(/(\d\d?)[-\/\.](\d\d?)[-\/\.](\d{4})/, function ($0, $m, $d, $y) {
            // temporary holder for swapping below
            var t = $d;
            // if the month is not first, we'll swap month and day...
            if (!natDateMonthFirst) {
                // ...but only if the day value is under 13.
                if (Number($d) < 13) {
                    $d = $m;
                    $m = t;
                }
            } else if (Number($m) > 12) {
                // Otherwise, we might still swap the values if the month value is currently over 12.
                $d = $m;
                $m = t;
            }
            // return a standardized format.
            return $y + '-' + $m + '-' + $d;
        });
    },

    // Fix numbers to be correctly padded
    fixNumbers = function (value) {
        if (!value) return false;

        // First, look for anything in the form of d.d or d.d.d...
        return value.replace(/(\d+)((\.\d+)+)?/g, function ($0, integer, decimal, $3) {
            // If there's more than 2 sets of numbers...
            if (decimal !== $3) {
                // treat as a series of integers, like versioning,
                // rather than a decimal
                return $0.replace(/(\d+)/g, function ($d) {
                    return padding($d) + $d
                });
            } else {
                // add a decimal if necessary to ensure decimal sorting
                decimal = decimal || ".0";
                return padding(integer) + integer + decimal + padding(decimal);
            }
        });
    },

    // Finally, this function puts it all together.
    natValue = function (value) {
        if (natCache[value]) {
            return natCache[value];
        }
        var newValue = fixNumbers(fixDates(value));
        return natCache[value] = newValue;
    };

    // The actual object used by this service
    return {
        naturalValue: natValue,
        naturalSort: function (a, b) {
            a = natVale(a);
            b = natValue(b);
            return (a < b) ? -1 : ((a > b) ? 1 : 0)
        }
    };
}])
.filter('dateToISO', function (convenienceMethods) {
    return function (input, object, propertyName, setToString) {
        return convenienceMethods.dateToIso(input, object, propertyName, setToString);
    };
})
.filter('dateToIsoTime', function (convenienceMethods) {
    return function (input) {
        if (!input) return "N/A";
        // Split timestamp into [ Y, M, D, h, m, s ]
        var t = input.split(/[- :]/);
        if (t[0] == "0000") return "N/A";
        // Apply each element to the Date function
        var d = new Date(t[0], t[1] - 1, t[2], t[3], t[4]);

        //at times like these, it's important to consider the nature of addition, concatonation and the universe in general.
        //input = d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear() + ' ' + d.getHours() + ':' + d.getMinutes() d.;
        input = d.toLocaleString();
        
        return input;
    };
})
.filter('activeOnly', function() {
    return function(array, reverse) {
            if (!array) return;
            if ("undefined" === typeof reverse) var reverse = false;
            var activeObjects = [];

            var i = array.length;
            while (i--) {
                if (array[i].Is_active == !reverse) activeObjects.unshift(array[i]);
        }
        return activeObjects;
    };
})
.filter('tel', function () {
    return function (phoneNumber) {
        if (!phoneNumber)
            return phoneNumber;

        return formatLocal('US', phoneNumber);
    }
})
.filter("sanitize", ['$sce', function($sce) {
    return function(htmlCode){
        return $sce.trustAsHtml(htmlCode);
    }
}])
.filter("isAre", function(){
    return function(data){
        return (data || []).length == 1 ? 'is' : 'are';
    }
})
.directive('stickyHeaders', function($timeout){
    return {
        restrict: 'A',
        scope: {
            stickyTop: '@'
        },
        link: function(scope, elem, attrs) {
            if( isNaN(scope.stickyTop) ){
                scope.stickyTop = 22;
                console.debug("Default stickyHeader top to " + scope.stickyTop);
            }

            // Ensure table has 'sticky-headers' class
            $(elem).addClass('sticky-headers');

            let setStickyHeaderHeight = function () {
                // Find header rows
                let sticky_rows = elem.find('thead').find('tr');

                // Set base sticky position
                let ceiling = 22;

                sticky_rows.each( (idx, row) => {
                    // Stick the row to the current ceiling
                    $(row).find('th').css({ top: ceiling + 'px'});
                    // Increment ceiling by the row's height
                    ceiling += $(row).height();
                });
            }

            // Watch a dummy value to trigger
            scope.$watch('watch', function() {
                $timeout(function(){
                    setStickyHeaderHeight();
               },300);

            });
        }
    };
})
.directive('hubBannerNav', function(){
    return {
        restrict: 'E',
        transclude: true,
        replace: true,
        scope: {
            /**
             * Array of objects which define the following fields:
             *   - route: Router path for the view, passed to $location service.
             *   - name:  Name of the view, rendered as link text.
             * Items which do not define these fields are treated as
             * non-functional separators.
             */
            hubViews: "=",
            hubNavNotifications: "=",
            hubIcon: "@",
            hubImage: "@",
            hubTitle: "@",
            hubSubtitle: "@"
        },
        template:   `<div class="hub-banner no-print">
                        <i ng-if="hubIcon" class="title-icon {{hubIcon}}"></i>
                        <img ng-if="hubImage" class="title-icon" ng-src="{{hubImage}}"/>

                        <span style="flex-direction: column; align-items: flex-start;">
                            <h1>{{hubTitle}}</h1>
                            <h4 ng-if="hubSubtitle">{{hubSubtitle}}</h4>
                        </span>

                        <ul class="banner-nav" ng-transclude ng-if="!hubViews.length">
                        </ul>

                        <ul class="banner-nav" >
                            <li ng-repeat="view in hubViews">
                                <span ng-if="!view.route">|</span>
                                <a  ng-if="view.route"
                                    ng-click="setRoute(view.route)"
                                    ng-href="#{{view.route}}"
                                    ng-class="{'active-nav': selectedRoute == view.route}">
                                    <span>{{view.name}}</span>
                                    <span class="nav-notification-badge"
                                          ng-repeat="notice in hubNavNotifications | filter:{name:view.id}:true"
                                          ng-if="notice.count">
                                        <span class="nav-notification-count">
                                            {{notice.count | maxNum:9}}
                                        </span>
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a class="home-link" ng-href="{{appRoot}}">
                                    <i class="icon-home"></i>
                                </a>
                            </li>
                        </ul>
                    </div>`,
        link: function(){},
        controller: function($scope, $location){
            console.debug($scope);
            $scope.selectedRoute = $location.path();
            $scope.appRoot = window.GLOBAL_WEB_ROOT;

            $scope.setRoute = function( route ){
                if( route ){
                    $scope.selectedRoute = route;
                }

                $location.path($scope.selectedRoute);
            };
        }
    };
})
/**
 * Display icon (and, optionally, Label) for a given Room Type
 */
.directive('roomTypeIcon', function(){
    return {
        restrict: 'E',
        replace: true,
        scope: {
            room: "=",
            roomType: "=",
            roomTypeName: "=",

            showTypeLabel: "@"
        },
        template:
        `<span>
            <img ng-if="type.img_src" width="15px;" ng-src="{{type.img_src}}"/>
            <i ng-if="type.icon_class" class="{{type.icon_class}}"></i>
            <span ng-if="showTypeLabel">{{type.label}}</span>
        </span>`,
        link: function(scope, elem, attrs){
            if( !Constants.ROOM_TYPE ){
                console.error("No room type constants defined");
                return;
            }
            else if( scope.roomType ){
                scope.type = scope.roomType;
            }
            else if( scope.roomTypeName ){
                scope.type = Constants.ROOM_TYPE[scope.roomTypeName];
            }
            else if( scope.room ){
                scope.type = Constants.ROOM_TYPE[scope.room.Room_type];
            }
            else {
                console.warn("No room type source in scope");
                return;
            }
        }
    };
})
.directive('roomList', function(){
    return {
        restrict: 'E',
        scope: {
            rooms: "="
        },
        template:
        `<div class="room-list" ng-repeat="(buildingName, rooms) in rooms | groupBy:'Building_name'">
            <span class="room-list-building">{{buildingName}}:</span>
            <span class="comma-separated">
                <span ng-repeat="room in rooms | orderBy:'Name'" class="subject">
                    <room-type-icon room-type-name="room.Room_type"></room-type-icon>
                    <span once-text="room.Name"></span>
                </span>
            </span>
        </div>`
    };
})
.directive('scrollTable', ['$window', '$location', '$rootScope', '$timeout', function($window, $location, $rootScope,$timeout) {
    return {
        restrict: 'A',
        scope: {
            watch: "="
        },
        link: function(scope, elem, attrs) {
             $(document).find('.container-fluid').prepend(
                '<div class="hidey-thing"></div>'
             )
             $('body').css({"minHeight":0})
             $(elem[0]).addClass('scrollTable');
             $(elem[0]).find('tbody').css({"marginTop": $(elem[0]).find('thead').height()});

             var setWidths = function () {
                 console.log("fired width setter")
                 var firstRow;
                 if (elem.find('tbody').find('tr:nth-child(3)').length) {
                     firstRow = elem.find('tbody').find('tr:nth-child(3)');
                 } else {
                     console.log("here");
                     firstRow = elem.find('tbody').find('tr:first');
                 }

                 if( !firstRow.length ){
                    console.debug("No rows to check size");
                    return;
                 }

                 console.debug("First real row:", firstRow[0]);

                 // For each row, assign widths to headers based on matching cols
                var headerRows = $(elem).find('thead').find("tr");
                console.debug("Header Rows: ", headerRows);

                headerRows.each(function(ridx, row) {
                    console.debug("Resizing row", row);

                    var idxModifier = 0;
                    $(row).find("th").each(function(index, head) {
                        // Check for colspan
                        var colspan = parseInt($(head).attr("colspan")) || 1;

                        // get 'colspan' number of columns, starting at index+idxModifier (to account for prior colspans)
                        var start = index + idxModifier;
                        var end = start + (colspan);

                        var cols = firstRow.children("td").slice(start, end);

                        console.debug(cols);

                        // Calculate total width of spanned columns
                        var totalWidth = cols
                            .map( (i, c) => $(c).width() )
                            .get()
                            .reduce( (acc, cur) => acc + cur, 0);

                        // Adjust idxModifier for further iterations
                        // We only want this adjusted for colspans greater than 1, so we reduce this -1
                        idxModifier += colspan - 1;

                        console.debug(index + " (" + start + ':' + end + ")",
                            this, "=>", cols,
                            "Width: " + totalWidth
                        );

                        $(this).width( totalWidth );
                    });
                });

                $(elem[0]).find('> tbody').css({"marginTop": $(elem[0]).find('thead').height()});

             }
             $(window).load(function() {setWidths();});

             scope.$watch('watch', function() {
                 //console.log('length changed')
                 $timeout(function(){
                    setWidths();
                },300)

             });
             angular.element($window).bind('resize', function() {setWidths();})
        }
    }
}])
.directive('ulTableHeights', ['$window', '$location', '$rootScope', '$timeout', function ($window, $location, $rootScope, $timeout) {
    return {
        restrict: 'C',
        scope: {
            watch: "=",
            otherwatch: "="
        },
        link: function (scope, elem, attrs) {
            scope.attrs = attrs;
            scope.$watch('attrs', function (oldVal, newVal) {
                if (!newVal || newVal == 0) return false;
                resize(attrs, elem, newVal)
            });

            resize(attrs, elem, scope.watchedThing);

            function resize(attrs, elem) {
                var len = elem.find('ul').length;
                if (!attrs.h) {
                    attrs.$set('h', elem.outerHeight());
                    attrs.$set('len', len);
                }
                elem.find('ul > li').css({ 'paddingTop': (attrs.h / (len)) - 17 + 'px', 'paddingBottom': (attrs.h / (len)) -8 + 'px', 'height': 0 });
            }
        }
    }
}])
.filter('toArray', function () {
    return function (object) {
        var array = [];
        for (var prop in object) {
            array.push(object[prop]);
        }
        return array;
    }
})
.filter('propsFilter', function () {

  return function(items, props) {
      var out = [];
      if (!items || !props) return out;
      var keys = Object.keys(props);
      if (keys[0].indexOf(".") > 0) {
          var properties = keys[0].split('.');
      } else {
          var properties = keys;
      }
    if (angular.isArray(items)) {
      items.forEach(function(item, key) {
        var itemMatches = false;
        if (item && item != null) {
            var myResultItem = item;

            for (var i = 0; i < properties.length; i++) {
                if (myResultItem[properties[i]]) {
                    myResultItem = myResultItem[properties[i]];
                }
            }
            if (myResultItem) {
                var text = props[properties.join('.')].toLowerCase();
                if (myResultItem.toString().toLowerCase().indexOf(text) !== -1) itemMatches = true;
            }

            if (itemMatches) {
                out.push(item);
            }
        }
      });
    } else {
      // Let the output be the input untouched
      out = items;
    }
    return out;
  }
})
.filter('roundFloat', function () {
    return function (item) {
        var number = parseFloat(item);
        return Math.round(number * 100000) / 100000 || "0";
    }
})
.filter('getDueDate', function () {
    return function (input) {
        var date = new Date(input);
        var duePoint = date.setDate(date.getDate() + 14);
        dueDate = new Date(duePoint).toISOString();
        return dueDate;
    };
})
.filter('getMonthName', function () {
    var monthNames = [{ val: "01", string: "January" },
                { val: "02", string: "February" },
                { val: "03", string: "March" },
                { val: "04", string: "April" },
                { val: "05", string: "May" },
                { val: "06", string: "June" },
                { val: "07", string: "July" },
                { val: "08", string: "August" },
                { val: "09", string: "September" },
                { val: "10", string: "October" },
                { val: "11", string: "November" },
                { val: "12", string: "December" }]
    return function (input) {
        var i = monthNames.length;
        while (i--) {
            if (input == monthNames[i].val) return monthNames[i].string;
        }
    };
})
//is a user a lab contact?  run this fancy filter to find out.
.filter('isContact',[function(){
  return function(users){
    if(!users)return;
    var contacts = [];
    var i = users.length
    while(i--){
        var j = users[i].Roles.length;
        while(j--){
            if(users[i].Roles[j].Name == Constants.ROLE.NAME.LAB_CONTACT){
                contacts.unshift(users[i]);
                break;
            }
        }
    }
    return contacts;
    }



    }])
    //sorts strings including those that have
    .filter('sortAlphanumeric', [function () {
        return function (item, field) {
            return naturalService.naturalValue(item[field]);
        }
    }])
    .filter("paginationFilter", function () {
        return function (items, start, limit) {
            if (!items || !Array.isArray(items)) return;
            if (limit - start > items.length) return items;
            console.log(start, limit, start + limit)
            return items.slice(start, start+limit);
        }
    })

    /**
     * Reduce a number to a maximum value if the number is greater.
     *  `20 | maxNum:5` will return "5+"
     *  `20 | maxNum:20` will return "20"
     *  `20 | maxNum:50` will return "20"
     */
    .filter('maxNum', function(){
        return function( num, max ){
            if( !num || !max )
                return num;

            if( num > max )
                return max + "+";
            else
                return num;
        }
    })
    .controller('WarnRoomRemoveCtrl', function ($scope, $rootScope,room, behavior, $q, $http, $modalInstance, convenienceMethods, room) {

        $scope.room = room;
        
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }


    });

