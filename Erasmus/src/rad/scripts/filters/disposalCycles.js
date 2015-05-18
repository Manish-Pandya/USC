angular.module('00RsmsAngularOrmApp')
	.filter('disposalCycles', function(convenienceMethods) {
	  return function(cycles) {
	  		console.log(convenienceMethods);
	  		if(!cycles) return;
	  		var disposalCycles = [];
	  		var i = cycles.length;
	  		while(i--){
	  			var cycle = cycles[i];
  				if(cycle.Status.toLowerCase() == "decaying" 
  					|| cycle.Status.toLowerCase() == "at rso" 
  					|| cycle.Status.toLowerCase() == "picked up")
  				{  	

  					if(cycle.Pour_allowed_date){
  						pourDay = convenienceMethods.getDate(cycle.Pour_allowed_date)
  						var pourSeconds = pourDay.getTime();
  						var now = new Date(),
					    beginningOfPourDay = new Date(
					        pourDay.getFullYear(),
					        pourDay.getMonth(),
					        pourDay.getDate(),
					        0,0,0);

					    console.log(now.getTime());
  						if(beginningOfPourDay.getTime() <= now.getTime())cycle.pourable = true;
  					}

  					disposalCycles.unshift(cycle);
	  			}
	  		}
	  		return disposalCycles;
	  };
	})