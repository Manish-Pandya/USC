'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var ParcelUse = function() {};
ParcelUse.prototype = {
    eagerAccessors:[
        { method: "loadParcelUseAmounts", boolean: 'Key_id' },
        { method: "loadDestinationParcel", boolean: 'Destination_parcel_id' }

    ],
    AmountsRelationship:{
        className: 	  'ParcelUseAmount',
        keyReference:  'Parcel_use_id',
        queryString:  'getParcelUseAmountById',
        paramValue: 'Key_id',
        queryParam:   ''
    },
    DestiantionParcelRelationship: {
        className: 'Parcel',
        keyReference: 'Destination_parcel_id',
        queryString: 'getUserById',
        queryParam: ''
    },
    loadParcel: function() {
        if(!this.Parcel) {
            dataLoader.loadChildObject(this, 'Parcel', 'Parcel', this.Parcel_id);
        }
    },
    loadParcelUseAmounts: function() {
        if(!this.ParcelUseAmounts) {
            console.log('hello');
            dataLoader.loadOneToManyRelationship(this,"ParcelUseAmounts",this.AmountsRelationship);
        }
    },
    getIsPickedUp: function () {
        if (!this.IsPickedUp) {
            this.IsPickedUp = true;
            if (!this.ParcelUseAmounts.length) {
                this.IsPickedUp = false;
            }
            for (var i = 0; i < this.ParcelUseAmounts.length; i++){
                if (!this.ParcelUseAmounts[i].IsPickedUp) {
                    this.IsPickedUp = false;
                    break;
                }
            }
        }
        return this.IsPickedUp;
    },

    loadDestinationParcel:  function() {
        if (this.Destination_parcel_id) {
            dataLoader.loadChildObject(this, 'DestinationParcel', 'Parcel', this.Destination_parcel_id);
        }
    }
}

// inherit from GenericModel
extend(ParcelUse, GenericModel);