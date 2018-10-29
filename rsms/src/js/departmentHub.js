angular.module('departmentHub', ['ui.bootstrap', 'convenienceMethodWithRoleBasedModule','ngRoute','once'])

.filter('specialtyLab_trueFalse', function(){
  return function(departments, bool){
    if(!departments)return;
    if(bool == null)bool = true;
    var changedThings = [];
    var i = departments.length;
    while(i--){
      if( departments[i].Specialty_lab == bool || (departments[i].Specialty_lab == null && !bool) ){
        changedThings.push(departments[i]);
      }
    }
    return changedThings;
  }
})
.filter("matchCampus", function(){
    return function(departments, campusName){
        if(!departments)return;
        if(!campusName)return departments;
        var i = departments.length;
        var matches = [];
        while(i--){
            if(departments[i].Campus_name == campusName){
                matches.unshift(departments[i]);
            }
        }
        return matches;
    }
})
.filter("removeNulls", function(){
    return function(departments){
        if(!departments)return;
        var i = departments.length;
        var matches = [];
        while(i--){
            var noPush = false;
            if(!departments[i].Campus_name){
                var j = departments.length
                while(j--){
                    if(j != i && departments[j].Department_name == departments[i].Department_name){
                        noPush = true;
                        break;
                    }
                }
            }
            if(!noPush){
                matches.push(departments[i]);
            }
        }
        return matches;
    }
})
.factory('departmentFactory', function(convenienceMethods,$q){
    var factory = {};
    var inspection = {};
    factory.getAllDepartments = function(url){
        var url = "../../ajaxaction.php?action=getAllDepartmentsWithCounts&callback=JSON_CALLBACK";
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
    factory.getAllCampuses = function(){
        var url = "../../ajaxaction.php?action=getAllCampuses&callback=JSON_CALLBACK";
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
    return factory;
});

departmentHubController = function($scope,departmentFactory,convenienceMethods, $modal){

    function init(){
        departmentFactory.getAllCampuses()
            .then(
                function(campuses){
                    $scope.campuses = campuses;
                    getDepartments();
                }
            )
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
        department.isDirty = true;
        $scope.error = '';
        deptDto = {
            Class: "Department",
            Name: department.Department_name,
            Is_active: !department.Is_active,
            Key_id: department.Department_id,
            Specialty_lab: department.Specialty_lab
        }
        departmentFactory.saveDepartment(deptDto).then(
          function(promise){
              console.log(promise);
              department.Is_active = promise[0].Is_active;
              department.isDirty = false;

          },
          function(promise){
            $scope.error = 'There was a promblem saving the department.';
            department.isDirty = false;
          }
        );

    }

    $scope.openModal = function(dto, isSpecialtyLab){
        var instance = $modal.open({
            templateUrl: 'departmentModal.html',
            controller: 'modalCtrl',
            resolve: {
                departmentDto: function(){
                   if(dto) return dto;
                   return {};
                },
                specialtyLab:function(){
                    return isSpecialtyLab;
                }
            }
        });
        instance.result.then(function (returnedDto) {
            if(!convenienceMethods.arrayContainsObject($scope.departments,returnedDto, ["Department_id", "Department_id"])){
                $scope.departments.push(returnedDto)
            }else{
                angular.extend(dto, returnedDto);
            }
        });
    }
}
modalCtrl = function($scope, departmentDto, specialtyLab, $modalInstance, departmentFactory, convenienceMethods){
    $scope.department = {
        Class: "Department",
        Name:'',
        Is_active:true,
        Specialty_lab:specialtyLab
    }
    $scope.specialtyLab = specialtyLab;

    if(departmentDto.Department_id){
        $scope.department.Name =   departmentDto.Department_name;
        $scope.department.Key_id = departmentDto.Department_id;
        $scope.deptName = departmentDto.Department_name;
    }
    // overwrites department with modified $scope.departmentCopy
    // note that the department parameter is the department to be overwritten.
    $scope.saveDepartment = function(){
        var prevent = false;
        var depts = departmentFactory.getDepartments();
        for(var i = 0; i < depts.length; i++){
            if((depts[i].Department_name.toLowerCase() == $scope.department.Name.toLowerCase()) && (!$scope.department.Key_id || $scope.department.Key_id != depts[i].Department_id)){
                prevent = true;
            }
        }
        
        
        // prevent user from changing name to an already existing department
        if(prevent)         {
            $scope.error = "Department with name " + $scope.department.Name + " already exists!";
            // TODO: sort out department vs $scope.departmentCopy (ie department passed in, but still has to use departmentCopy, a scope variable)
            // Mixed up here, later in this method, and in departmentHub.php itself.
            return false;
        }
        $scope.isDirty = true;
        $scope.error = '';
        departmentFactory.saveDepartment($scope.department).then(
          function(promise){
              console.log(promise);
              $modalInstance.close(promise[0]);
          },
          function(promise){
            $scope.error = 'There was a promblem saving the department.';
            $scope.isDirty = false;
          }
        );
    }

    $scope.cancel = function(){
        $scope.error = '';
        $modalInstance.dismiss();
    }

}
