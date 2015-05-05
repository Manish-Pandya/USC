var roleBased = angular.module('roleBased', ['ui.bootstrap','convenienceMethodModule'])
	.directive('roles', ['roleBasedFactory', function(roleBasedFactory) {
    return {
        restrict: 'A',
        link: function(scope, elem, attrs, test) {
           console.log(scope);
           console.log(roleBasedFactory);
           console.log(elem);
           console.log(test);
	    }
	 }
	}])

	.factory('roleBasedFactory', function(convenienceMethods,$q, $rootScope){
		var factory = {};
		factory.roles = [];

		factory.getCurrentRoles = function()
		{
		  var deferred = $q.defer();
		  //lazy load
		  if(factory.users.length){
		    deferred.resolve(factory.users);
		    return deferred.promise;
		  }

		  var url = '../../ajaxaction.php?action=getCurrentUser&callback=JSON_CALLBACK';
		    convenienceMethods.getDataAsDeferredPromise(url).then(
		    function(user){
		      roles = user.Roles;
		      deferred.resolve(roles);
		      $rootScope.roles = roles;
		    },
		    function(promise){
		      deferred.reject();
		    }
		  );
		  return deferred.promise;
		}

		factory.setRole = function(role){
			factory.roles = [role];
		}

		return factory;
	})

