angular.module('00RsmsAngularOrmApp')
	.filter('needsWipeTest', function() {
	  return function(parcels) {
	  		if(!parcels)return;
	  		var parcelsThatNeedWipeTests = [];
	  		var i = parcels.length;

	  		while(i--){
	  			var parcel = parcels[i];
  				if( parcel.Status && parcel.Status == Constants.PARCEL.STATUS.ARRIVED || parcel.Status == Constants.PARCEL.STATUS.PRE_ORDER || parcel.Status == "" ){
  					parcelsThatNeedWipeTests.unshift(parcel);
	  			}
	  		}

	  		return parcelsThatNeedWipeTests;
	  };
	})