angular.module('00RsmsAngularOrmApp')
	.filter('miscWipeTests', function() {
	  return function(tests) {
	  		if(!tests)return;
	  		var availableTests = [];
	  		var i = tests.length;

	  		while(i--){
	  			var test = tests[i];
  				if(test.Is_active == true && (!test.Closeout_date || test.Closeout_date == "0000-00-00 00:00:00") ){
  					console.log(test)
  					availableTests.push(test);
	  			}
	  		}
	  		console.log(availableTests);
	  		return availableTests;
	  };
	})