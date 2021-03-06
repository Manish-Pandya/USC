'use strict';
/* Auto-generated stub file for the SolidsContainer class. */

//constructor
var SolidsContainer = function() {};
SolidsContainer.prototype = {
	eagerAccessors:[/*{method:"loadWasteBagsForPickup", boolean: 'WasteBagsForPickup'}*/],

	WasteBagsForPickupRelationship: {
	    className: 'WasteBag',
	    keyReference: 'Container_id',
	    paramValue: 'Key_id',
	    paramName: 'id',
	    where: [{ 'Pickup_id': "IS NULL" }, { 'Date_removed': "NOT NULL" }]
	},

	CurrentWasteBagsRelationship: {
	    className: 'WasteBag',
	    keyReference: 'Container_id',
	    paramValue: 'Key_id',
	    paramName: 'id',
	    where: [{ 'Date_removed': "IS NULL" }]
	},

	loadRoom: function(){
		if(!this.Room && this.Room_id) {
            dataLoader.loadChildObject( this, 'Room', 'Room', this.Room_id );
        }
	},

	loadWasteBagsForPickup: function () {
        //alert('?')
	   // this.WasteBagsForPickup = [];
	    dataLoader.loadOneToManyRelationship(this, 'WasteBagsForPickup', this.WasteBagsForPickupRelationship, this.WasteBagsForPickupRelationship.where);
	},	
    
	loadCurrentWasteBags: function () {
	   // this.CurrentWasteBags = [];
	    dataLoader.loadOneToManyRelationship(this, 'CurrentWasteBags', this.WasteBagsForPickupRelationship, this.CurrentWasteBagsRelationship.where);
	}

}

// inherit from GenericModel
extend(SolidsContainer, GenericModel);