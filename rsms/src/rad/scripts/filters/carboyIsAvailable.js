angular.module('00RsmsAngularOrmApp')
    .filter('carboyIsAvailable', function() {
      return function(carboys) {
            if(!carboys) return;
              var availableCarboys = [];
              var i = carboys.length;

              while(i--){
                  var carboy = carboys[i];
                  if(carboy.Is_active == true && carboy.Status == Constants.CARBOY_USE_CYCLE.STATUS.AVAILABLE){
                      availableCarboys.unshift(carboy);
                  }
              }

              return availableCarboys;
      };
    })
