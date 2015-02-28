angular.module('00RsmsAngularOrmApp')
	.filter('parcelParser', function() {
	  return function(uses) {
	  		if(!uses)return;
	  		var j = uses.length;
	  		var filteredUses = [];
	  		while(j--){
	  			var use = uses[j];
	  			use.Solids = [];
	  			use.Liquids = [];
	  			use.Vials = [];
		  		var i = use.ParcelUseAmounts.length;
		  		while(i--){
		  			var amt = use.ParcelUseAmounts[i];
		  			setTimeout(function(){
		  				console.log(amt);
			  			if(amt.Waste_type_id == 4)use.Solids.push(amt);
		  				if(amt.Waste_type_id == 3)use.Vials.push(amt);
		  				if(amt.Waste_type_id == 1)use.Liquids.push(amt);
		  			},100);
		  		}
  				filteredUses.unshift(use);
		  	}
			return filteredUses;
	  };
	})
