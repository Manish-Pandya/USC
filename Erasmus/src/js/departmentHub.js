angular.module('departmentHub', ['ui.bootstrap', 'convenienceMethodModule','ngRoute','once'])

.factory('departmentFactory', function(convenienceMethods,$q){
	var factory = {};
	var inspection = {};
	factory.getAllDepartments = function(url){
		var url = "../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK";
    	var deferred = $q.defer();

    	convenienceMethods.getDataAsDeferredPromise(url).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject(promise);
			}
		);
		return deferred.promise
	}
	factory.setDepartments = function(departments){
		this.departments = departments
	}

	factory.getDepartments = function(){
		return this.departments;
	}
	factory.editNoDepartments = function(){
		var len = this.departments.length;
		for(i=0;i<len;i++){
			this.departments[i].edit = false;
		}
		return this.departments;
	}
	factory.saveDepartment = function(department){
        var url = "../../ajaxaction.php?action=saveDepartment";
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url, department).then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject(promise);
          }
        );
        return deferred.promise
    }
	return factory;
});

departmentHubController = function($scope,departmentFactory,convenienceMethods){

    function init(){
    	getDepartments();
    }

    init();

    function getDepartments(){
    	departmentFactory.getAllDepartments().then(
    		function(promise){
    			departmentFactory.setDepartments(promise);
    			$scope.departments = departmentFactory.getDepartments();
    		},
    		function(promise){
    			$scope.error = 'The system couldn\'t get the list of departments.  Please check your internet connection and try again.'
    		}
    	)
    }

    $scope.editDepartment = function(department){
    	$scope.departments = departmentFactory.editNoDepartments();
    	department.edit = true;
        // create a copy of this department so we can cancel edit if necessary
    	$scope.departmentCopy = angular.copy(department);
    }

    $scope.saveDepartment = function(department){
    	department.isDirty = true;
    	console.log(department);

    }

    $scope.cancelEdit = function(department){
    	department.edit = false;
    	$scope.departmentCopy = {};
    	$scope.departments = departmentFactory.editNoDepartments();
    	$scope.creatingDepartment = false;
    	$scope.newDepartment = false;
    }

    $scope.createDepartment = function(){
    	$scope.creatingDepartment = true;
    	$scope.newDepartment = {
    		Name:'',
    		Class:'Department',
    		Is_active:true
    	}
    }

    $scope.handleActive = function(department){
    	$scope.departmentCopy = angular.copy(department);
    	$scope.departmentCopy.Is_active=!$scope.departmentCopy.Is_active;
    	$scope.saveDepartment(department);
    	department.setActive = true;
    }

    // overwrites department with modified $scope.departmentCopy
    // note that the department parameter is the department to be overwritten.
    $scope.saveDepartment = function(department){
    	console.log(department);

        // prevent user from changing name to an already existing department
        if(convenienceMethods.arrayContainsObject($scope.departments, $scope.departmentCopy, ['Name', 'Name'])) {
            $scope.error = "Department with name " + $scope.departmentCopy.Name + " already exists!";
            // TODO: sort out department vs $scope.departmentCopy (ie department passed in, but still has to use departmentCopy, a scope variable)
            // Mixed up here, later in this method, and in departmentHub.php itself.
            return false;
        }

    	if(!department.Class)department.Class="Department";
      	department.isDirty = true;
	    departmentFactory.saveDepartment($scope.departmentCopy).then(
	      function(promise){
	      	department.isDirty = false;
	      	department.edit = false;
	      	department.Name = promise.Name;
	      	department.Is_active = promise.Is_active;
	      	if(!convenienceMethods.arrayContainsObject($scope.departments,department))$scope.departments.push(department);
	      	departmentFactory.setDepartments($scope.departments);
	      	$scope.creatingDepartment = false;
    		$scope.newDepartment = false;
    		department.setActive = false;
	      },
	      function(promise){
	        department.error = 'There was a promblem saving the department.';
	    	department.isDirty = false;
	    	department.edit = false;
	    	$scope.creatingDepartment = false;
    		$scope.newDepartment = false;
    		$scope.department.setActive = false;

	      }
	    );
	}

	// adds a newly created department
	$scope.saveNewDepartment = function(department) {

        // Prevent user from saving a duplicate department
        if(convenienceMethods.arrayContainsObject($scope.departments, department, ['Name', 'Name'])) {
            $scope.error = "Department with name " + department.Name + " already exists!";
            return false;
        }


		department.isDirty = true;
		departmentFactory.saveDepartment(department).then(
			function(returnedData) {
				$scope.departments.push(department);
			},
			function(errorData) {
				department.error = 'There was a problem when saving the new department. See console log for details.';
				console.log('Server returned error: ');
				console.dir(errorData);
			})['finally'](function() { // note: odd ['finally'] syntax is so that it can be called in ie8.
				department.isDirty = false;
				$scope.creatingDepartment = false;
				$scope.newDepartment = false;
				department.setActive = false;
			})
	}
}