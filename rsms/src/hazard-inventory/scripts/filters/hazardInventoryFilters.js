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
          while(i--){
              //looking for closed inspections
              if(closedOrNot == true){
                  if(inspections[i].Cap_submitted_date)filteredInspections.push(inspections[i]);
              }
              //looking for open inspections
              else{
                if(!inspections[i].Cap_submitted_date)filteredInspections.push(inspections[i]);
              }
          }
          return filteredInspections;
      };
    })
