angular.module('filtersApp',[])
    .filter('splitAtPeriod', function() {
      return function(input) {
              if(!input)return "N/A";
            // Split timestamp into [ Y, M, D, h, m, s ]
            var split = input.split('.');
            // Apply each element to the Date function
            var string = '';
            var i = split.length;
            while(i--){
                string +=  split[i] + ' ';
            }

            return string;
      };
    })
