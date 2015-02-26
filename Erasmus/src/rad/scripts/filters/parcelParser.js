angular.module('00RsmsAngularOrmApp')
	.filter('parcelParser', function() {
	  return function(uses, test) {
	  		console.log(test);
	  		if(!uses)return;
	  		console.log(uses);
	  		var j = uses.length;
	  		var filteredUses = [];
	  		while(j--){
	  			var use = uses[j];
		  		console.log(use);
	  			use.Solids = [];use.Liquids = [];use.Vials = [];

		  		var i = use.ParcelUseAmounts.length;
		  		while(i--){
		  			var amt = use.ParcelUseAmounts[i];
		  			if(amt.Waste_type_id == 4)use.Solids.unshift(amt);
	  				if(amt.Waste_type_id == 3)use.Vials.unshift(amt);
	  				if(amt.Waste_type_id == 1)use.Liquids.unshift(amt);
	  				filteredUses.push(use);
		  		}
		  	}
		  	console.log(filteredUses);
			return filteredUses
	  };
	})
