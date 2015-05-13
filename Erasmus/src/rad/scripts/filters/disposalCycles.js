angular.module('00RsmsAngularOrmApp')
	.filter('disposalCycles', function() {
	  return function(cycles) {
	  		if(!cycles) return;
	  		var disposalCycles = [];
	  		var i = cycles.length;
	  		while(i--){
	  			var cycle = cycles[i];
  				if(cycle.Status.toLowerCase() == "decaying" 
  					|| cycle.Status.toLowerCase() == "at rso" 
  					|| cycle.Status.toLowerCase() == "picked up")
  				{
  					
  					disposalCycles.unshift(cycle);
	  			}
	  		}
	  		return disposalCycles;
	  };
	})