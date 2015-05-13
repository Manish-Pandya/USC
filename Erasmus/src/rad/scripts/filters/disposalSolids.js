angular.module('00RsmsAngularOrmApp')
	.filter('disposalSolids', function() {
	  return function(solids) {
	  		if(!solids) return;
	  		console.log(solids);
	  		var disposalSolids = [];
	  		var i = solids.length;
	  		while(i--){
	  			var solid = solids[i];
	  			console.log(solid);
  				if(solid.Pickup_id && !solid.Drum_id){
  					disposalSolids.unshift(solid);
	  			}
	  		}
	  		return disposalSolids;
	  };
	})