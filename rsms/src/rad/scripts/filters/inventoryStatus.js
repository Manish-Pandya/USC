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
	  					piInventory.Status = Constants.INVENTORY.STATUS.LATE;
	  				}else{
	  					piInventory.Status = Constants.INVENTORY.STATUS.COMPLETE;
	  				}
	  			}else{
	  				piInventory.Status = Constants.INVENTORY.STATUS.NA;
	  			}
	  			console.log(piInventory);
	  		}
	  		return piInventories;
	  };
	})