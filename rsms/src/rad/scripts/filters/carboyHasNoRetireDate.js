angular.module('00RsmsAngularOrmApp')
    .filter('carboyHasNoRetireDate', function() {
      return function(carboys) {
            if(!carboys) return;
              var availableCarboys = [];
              var i = carboys.length;

              while(i--){
                  var carboy = carboys[i];
                  if(!carboy.Retirement_date || carboy.Retirement_date == null){
                      availableCarboys.push(carboy);
                  }
              }

              return availableCarboys;
      };
    })