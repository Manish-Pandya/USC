angular.module('test', [])
.factory('testMethods', function(){
	return{
		test: function(){
			alert('test');
		}
	};

});