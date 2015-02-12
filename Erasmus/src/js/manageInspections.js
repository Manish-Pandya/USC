var monthNames = [ "January", "February", "March", "April", "May", "June",
    		"July", "August", "September", "October", "November", "December" ];
var monthNames2 = [{val:"01", string:"January"},
				{val:"02", string:"February"},
				{val:"03", string:"March"},
				{val:"04", string:"April"},
				{val:"05", string:"May"},
				{val:"06", string:"June"},
				{val:"07", string:"July"},
				{val:"08", string:"August"},
				{val:"09", string:"September"},
				{val:"10", string:"October"},
				{val:"11", string:"November"},
				{val:"12", string:"December"}]
var getDate = function(time){

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
			formattedTime.monthString = monthNames[date.getMonth()];
			//console.log(formattedTime);
			return formattedTime;
		}


var locationHub = angular.module('manageInspections', ['convenienceMethodModule','once','ui.bootstrap'])

.filter('genericFilter', function () {
	return function (items,search,convenienceMethods) {
		if(search){
			var i = 0;
			if(items)i = items.length;
			var filtered = [];

			var isMatched = function(input, item){
				if(item.Name == input)return true;
				return false;
			}

			while(i--){

				//we filter for every set search filter, looping through the collection only once
				var item=items[i];
				item.matched = true;

				if(search.building){
					if( item.Building_name && item.Building_name.toLowerCase().indexOf(search.building.toLowerCase() ) < 0 ){
						item.matched = false;
					}

				}

				if(search.inspector){
					if(item.Inspections){
						if(item.Inspections.Inspectors){
							var z = item.Inspections.Inspectors.length;
							var longString = "";
							while(z--){
								longString += item.Inspections.Inspectors[z].User.Name;
							}
							if(longString.toLowerCase().indexOf(search.inspector.toLowerCase()) < 0)item.matched = false;
						}else{
							item.matched = false;
						}
					
					}else{
						item.matched = false;
					}
					
				}

				if( search.campus ) {
					if(item.Campus_name.toLowerCase().indexOf(search.campus.toLowerCase()) < 0)item.matched = false;
				}

				if(search.pi && item.Pi_name){
					if(item.Pi_name.toLowerCase().indexOf(search.pi.toLowerCase()) < 0)item.matched = false;
				}

				if(search.status){
					if(item.Inspections)var status = item.Inspections.Status;
					if(!item.Inspections)var status = "Not Scheduled";
					if(status.toLowerCase().indexOf(search.status.toLowerCase()) < 0)item.matched = false;
				}

				if(search.date){
					if(!item.Inspections || !item.Inspections.Date_started && !item.Inspections.Schedule_month){
						item.matched = false;
					}else{
						if(item.Inspections && item.Inspections.Date_started)var tempDate = getDate(item.Inspections.Date_started);
						if(tempDate && tempDate.monthString.toLowerCase().indexOf(search.date) < 0  ){
							var goingToMatch = false;
						}else{
							var goingToMatch = true;
						}
						if(item.Inspections && item.Inspections.Schedule_month){
							console.log(item.Inspections.Schedule_month);
							var j = monthNames2.length
							while(j--){
								if(monthNames2[j].val == item.Inspections.Schedule_month){
									if(monthNames2[j].string.toLowerCase().indexOf(search.date.toLowerCase())>-1)var goingToMatch = true;
								}
							}
						}
						if(!goingToMatch)item.matched = false;
					}
				}


				if(item.matched == true)filtered.unshift(item);

			}
			return filtered;
		}else{
			return items;
		}
	};
})
.filter('getDueDate', function() {
  return function(input) {
    var date = new Date(input);
    var duePoint = date.setDate(date.getDate() + 14);
    dueDate = new Date(duePoint).toISOString();
    console.log(dueDate);
    return dueDate;
  };
})
.filter('dateToISO', function() {
  return function(input) {
    input = new Date(input).toISOString();
    return input;
  };
})
.filter('getMonthName', function(){
	return function(input){
		var i = monthNames2.length;
		while(i--){
			if(input == monthNames2[i].val)return monthNames2[i].string;
		}
	};
})
.factory('manageInspectionsFactory', function(convenienceMethods,$q,$rootScope){
	var factory = {};
	factory.InspectionScheduleDtos = [];
	factory.currentYear;
	factory.years = [];
	factory.Inspectors = [];
	factory.minYear = 2010;
	factory.months = [];

	factory.getCurrentYear = function()
	{
		//if we don't have a the list of pis, get it from the server
		var deferred = $q.defer();
		//lazy load
		if(this.years.length){
			deferred.resolve(this.years);
		}else{
			var url = '../../ajaxaction.php?action=getCurrentYear&callback=JSON_CALLBACK';
	    	convenienceMethods.getDataAsDeferredPromise(url).then(
				function(promise){
					deferred.resolve(promise);
				},
				function(promise){
					console.log('uh ih')
					deferred.reject();
				}
			);
		}

		deferred.promise.then(
			function( currentYear ){
				factory.currentYear = {Name: parseInt( currentYear )};
			}
		)

		return deferred.promise;

	}

	factory.getYears = function()
	{
		var defer = $q.defer();

		this.getCurrentYear()
			.then(
				function( currentYear ){
					var maxYear = parseInt(currentYear) + 1;
					var years = [];
					while( maxYear-- &&  maxYear >= factory.minYear ){
						var year = {Name: parseInt( maxYear )}
						years.push( year );
					}
					defer.resolve( years )
				},
				function( error ){

				}

			);

		defer.promise
			.then(
				function( years ){
					factory.years = years;
				}
			);
		return defer.promise;
	}

	factory.getInspectionScheduleDtos = function( year )
	{
			//if we don't have a the list of pis, get it from the server
			var deferred = $q.defer();
			//lazy load
			if(this.InspectionScheduleDtos.length){
				deferred.resolve(this.InspectionScheduleDtos);
			}else{
				var url = '../../ajaxaction.php?action=getInspectionSchedule&year='+year.Name+'&callback=JSON_CALLBACK';
		    	convenienceMethods.getDataAsDeferredPromise(url).then(
					function(promise){
						deferred.resolve(promise);
					},
					function(promise){
						console.log('usho')
						deferred.reject();
					}
				);
			}

			deferred.promise.then(
				function( InspectionScheduleDtos ){
					factory.InspectionScheduleDtos = {Name: parseInt( InspectionScheduleDtos )};
				},
				function(){
					alert('error getting schedule')
				}
			)

			return deferred.promise;
	}

	factory.getAllInspectors = function()
	{
			//if we don't have a the list of pis, get it from the server
			var deferred = $q.defer();
			//lazy load
			if(this.Inspectors.length){
				deferred.resolve(this.Inspectors);
			}else{
				var url = '../../ajaxaction.php?action=getAllInspectors&callback=JSON_CALLBACK';
		    	convenienceMethods.getDataAsDeferredPromise(url).then(
					function(promise){
						deferred.resolve(promise);
					},
					function(promise){
						console.log('usho')
						deferred.reject();
					}
				);
			}

			deferred.promise.then(
				function( inspectors ){
					factory.Inspectors = inspectors;
				}
			)

			return deferred.promise;
	}

	factory.getMonths = function()
	{
			this.months = [
				{val:"01", string:"January"},
				{val:"02", string:"February"},
				{val:"03", string:"March"},
				{val:"04", string:"April"},
				{val:"05", string:"May"},
				{val:"06", string:"June"},
				{val:"07", string:"July"},
				{val:"08", string:"August"},
				{val:"09", string:"September"},
				{val:"10", string:"October"},
				{val:"11", string:"November"},
				{val:"12", string:"December"},
			];

			return this.months;
	}

	factory.scheduleInspection = function( dto, year, inspectorIndex )
	{
			$rootScope.saving = true;
			$rootScope.error = null;
			if(!dto.Inspectors)dto.Inspectors = [];
			var inspectors = dto.Inspections ? dto.Inspections.Inspectors : [];
			if(inspectorIndex){
				factory.getAllInspectors()
					.then(
						function(allInspectors){
							inspectors.push( allInspectors[inspectorIndex] );
						}
					)
			}

			dto.Inspections = {
				Class: "Inspection",
				Key_id: dto.Inspection_id,
				Schedule_month: dto.Schedule_month,
				Schedule_year:  year.Name,
				Principal_investigator_id: dto.Pi_key_id,
				Inspectors: inspectors,
				Is_active: true
			}


			var url = '../../ajaxaction.php?action=scheduleInspection';
			convenienceMethods.saveDataAndDefer(url, dto)
				.then(
					function(inspection){
						dto.Inspections = inspection;
						dto.Inspection_id = inspection.Key_id;
						$rootScope.saving = false;
					},
					function(error){
						$rootScope.saving = false;
						$rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
					}
				);
	}

	return factory;
});


manageInspectionCtrl = function($scope, manageInspectionsFactory, convenienceMethods){
	
	$scope.mif = manageInspectionsFactory;
	$scope.convenienceMethods = convenienceMethods;
	$scope.years = [];


	var getDtos = function( year )
	{		
			return manageInspectionsFactory.getInspectionScheduleDtos( year )
				.then(
					function(dtos){
						$scope.dtos = dtos;
						$scope.loading = false;
					}
				)
	},

	getYears = function()
	{
			return manageInspectionsFactory.getYears()
				.then(
					function( years ){
						$scope.years = years;
						$scope.selectedYear = years[0];
						return $scope.selectedYear;
					},
					function( error ){
						$scope.error = 'Uh oh';
					}
				)
	},

	getAllInspectors = function()
	{
			return manageInspectionsFactory.getAllInspectors()
				.then(
					function(inspectors){
						$scope.inspectors = inspectors;
					}
				)
	},

	getMonths = function(){
			$scope.months = manageInspectionsFactory.getMonths();

	}

	init = function()
	{
			$scope.loading = true;
			getAllInspectors()
				.then(getYears)
				.then(getDtos)
				.then(getMonths)
	}


	init();
	

	$scope.selectYear = function()
	{
		manageInspectionsFactory.getInspectionScheduleDtos( $scope.selectedYear )
			.then(
				function( dtos ){
					$scope.dtos = dtos;
				},
				function( error ){
					$scope.error = "The system could not retrieve the list of inspections for the selected year.  Please check your internet connection and try again."
				}
			)
	}


}