angular
    .module('HazardInventory')
    .filter("Is_equipment", function(){
        return function(hazards, bool){
            if(!hazards)return;
            var matches = [];
            var i = hazards.length;
            while(i--){
                if(hazards[i].Is_equipment == bool)matches.unshift(hazards[i]);
            }
            return matches;
        }
    })
    .filter('inspectionClosed', function () {
      return function (inspections, closedOrNot) {
          if(!inspections)return;
          var filteredInspections = [];
          var i = inspections.length;
          var now = new Date();
          var thisYear = now.getFullYear().toString();
          var aYearAgo = new Date().setMonth(now.getMonth() - 12);
          var lastYear = new Date(aYearAgo).getFullYear().toString();

          while(i--){
              //looking for closed inspections
              if(closedOrNot == true){
                  if(inspections[i].Cap_submitted_date)filteredInspections.push(inspections[i]);
              }
              //looking for open inspections
              else {
                if(!inspections[i].Cap_submitted_date){
                    filteredInspections.push(inspections[i]);
                } else {
                  var inspectionYear =  inspections[i].Cap_submitted_date.split(/[- :]/)[0];
                  if(inspectionYear == thisYear || inspectionYear == lastYear) filteredInspections.push(inspections[i]);
                }
              }
          }
          return filteredInspections;
      };
    })
    .filter('getMonthName', function(){
        return function(input){
            var monthNames = Constants.INSPECTION.MONTH_NAMES
            var i = monthNames.length;
            while(i--){
                if(input == monthNames[i].val)return monthNames[i].string;
            }
        };
    })
