angular
    .module('HazardInventory')
    .filter("Is_equipment", function () {
        return function (hazards, bool) {
            if (!hazards) return;
            var matches = [];
            var i = hazards.length;
            while (i--) {
                if (hazards[i].Is_equipment == bool) matches.unshift(hazards[i]);
            }
            return matches;
        }
    })
    .filter("roomIdMatches", function () {
        return function (phrs, roomId, piId) {
            if (!phrs) return;
            var matches = [];
            var i = phrs.length;
            while (i--) {
                if (phrs[i].Principal_investigator_id == piId) phrs[i].current = true;
                if (phrs[i].Room_id == roomId) {
                    matches.unshift(phrs[i]);
                }
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
              if (closedOrNot == true) {
                  if (inspections[i].Cap_submitted_date
                      || inspections[i].Date_closed
                      || (inspections[i].Date_started && inspections[i].Date_started.split(/[- :]/)[0] != thisYear)) {
                      filteredInspections.push(inspections[i]);
                  }
              }
              //looking for open inspections
              else {                
                var inspectionYear = inspections[i].Cap_submitted_date? inspections[i].Cap_submitted_date.split(/[- :]/)[0] : null;
                if ((inspectionYear && inspectionYear == thisYear)
                    || inspections[i].Schedule_year == thisYear
                    || (inspections[i].Date_started && inspections[i].Date_started.split(/[- :]/)[0] == thisYear)) {

                    filteredInspections.push(inspections[i]);
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

    .filter('relevantRooms', function () {
        return function (rooms) {
            if (!rooms) return;
            var l = rooms.length;
            var relevantRooms = rooms.filter(function (room) {
                return room.ContainsHazard || room.OtherLab
            })
            return relevantRooms;
        }
    })
