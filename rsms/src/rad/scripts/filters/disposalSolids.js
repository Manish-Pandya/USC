angular.module('00RsmsAngularOrmApp')
	.filter('disposalSolids', function() {
	  return function(solids) {
	  		if(!solids) return;
	  		var disposalSolids = [];
	  		var i = solids.length;
	  		while(i--){
	  			var solid = solids[i];
  				if(solid.Pickup_id && !solid.Drum_id){
  					disposalSolids.unshift(solid);
	  			}
	  		}
	  		return disposalSolids;
	  };
	})