angular.module('00RsmsAngularOrmApp')
	.filter('inventoryStatus', function(convenienceMethods) {
	  return function(piInventories, inventory) {
	  		if(!piInventories)return;
	  		var i = piInventories.length;
	  		if(inventory.Due_date)var dueDate = convenienceMethods.getDate(inventory.Due_date);
			if(!dueDate){
				alert('no due date');
				return piInventories;
			}
			var curDate = new Date();
	  		while(i--){
	  			var piInventory = piInventories[i];
	  			if(dueDate.getTime() < curDate.getTime()){
	  				if(!piInventory.Sign_off_date){
	  					piInventory.Status = 'Late';
	  				}else{
	  					piInventory.Status = 'Complete';
	  				}
	  			}else{
	  				piInventory.Status = "N/A"
	  			}
	  			console.log(piInventory);
	  		}
	  		return piInventories;
	  };
	})