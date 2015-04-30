angular.module('00RsmsAngularOrmApp')
	.filter('needsWipeTest', function() {
	  return function(parcels) {
	  		if(!parcels)return;
	  		var parcelsThatNeedWipeTests = [];
	  		var i = parcels.length;

	  		while(i--){
	  			var parcel = parcels[i];
  				if( parcel.Status && parcel.Status.toLowerCase() == "arrived" || parcel.Status.toLowerCase() == "pre-order" || parcel.Status.toLowerCase() == "" ){
  					parcelsThatNeedWipeTests.unshift(parcel);
	  			}
	  		}

	  		return parcelsThatNeedWipeTests;
	  };
	})