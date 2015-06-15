angular.module('00RsmsAngularOrmApp')
	.filter('carboyIsAvailable', function() {
	  return function(carboys) {
	  		var availableCarboys = [];
	  		var i = carboys.length;

	  		while(i--){
	  			var carboy = carboys[i];
  				if(carboy.Is_active == true && carboy.Status == "Available"){
  					availableCarboys.unshift(carboy);
	  			}
	  		}

	  		return availableCarboys;
	  };
	})