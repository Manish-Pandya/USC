angular.module('00RsmsAngularOrmApp')
	.filter('activePickups', function() {
	  return function(pickups) {
	  		if(!pickups)return;
	  		var activePickups = [];
	  		var i = pickups.length;

	  		while(i--){
	  			var pickup = pickups[i];
  				if(pickup.Status == Constants.PICKUP.STATUS.PICKED_UP || pickup.Status == Constants.PICKUP.STATUS.REQUESTED && pickup.Waste_bags.length || pickup.Scint_vial_collections.length || pickup.Carboy_use_cycles.length){
  					activePickups.push(pickup);
	  			}
	  		}
	  		return activePickups;
	  };
	})