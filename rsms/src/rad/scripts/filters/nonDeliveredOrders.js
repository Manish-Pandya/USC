angular.module('00RsmsAngularOrmApp')
    .filter('notDelivered', function() {
      return function(parcels) {
              if(!parcels)return;
              var j = parcels.length;
              var filtered = [];
              var matchedStatuses = [
                Constants.PARCEL.STATUS.REQUESTED,
                Constants.PARCEL.STATUS.ARRIVED,
                Constants.PARCEL.STATUS.PRE_ORDER,
                Constants.PARCEL.STATUS.ORDERED,
                Constants.PARCEL.STATUS.WIPE_TESTED,
              ]
              while(j--){
                  if(matchedStatuses.indexOf(parcels[j].Status) > -1)  {
                      filtered.unshift(parcels[j]);
                  }
              }
            return filtered;
      };
    })
    .filter('pisNeedingPackages', function() {
      return function(pis) {
              if(!pis)return;
              var j = pis.length;
              var filtered = [];
              var matchedStatuses = [
                Constants.PARCEL.STATUS.REQUESTED,
                Constants.PARCEL.STATUS.ARRIVED,
                Constants.PARCEL.STATUS.PRE_ORDER,
                Constants.PARCEL.STATUS.ORDERED,
                Constants.PARCEL.STATUS.WIPE_TESTED,
              ]
              while(j--){
                  if(pis[j].ActiveParcels){
                      var i = pis[j].ActiveParcels.length;
                      while(i--){
                        if(matchedStatuses.indexOf(pis[j].ActiveParcels[i].Status) > -1)  {
                          filtered.unshift(pis[j]);
                            break;
                        }
                      }
                  }
              }
            return filtered;
      };
    })
