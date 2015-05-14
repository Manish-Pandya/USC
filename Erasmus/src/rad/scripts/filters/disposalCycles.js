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
  						var date = new Date();
  						console.log(Date.parse(date));
  						var pourSeconds = Date.parse(convenienceMethods.getDate(cycle.Pour_allowed_date));
  						console.log(pourSeconds);
  						var now = new Date(),
					    then = new Date(
					        now.getFullYear(),
					        now.getMonth(),
					        now.getDate(),
					        0,0,0),
					    diff = now.getTime() - then.getTime()
  						if(pourSeconds-diff <= date)cycle.pourable = true;
  					}

  					disposalCycles.unshift(cycle);
	  			}
	  		}
	  		return disposalCycles;
	  };
	})