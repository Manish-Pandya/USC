'use strict';
/* Auto-generated stub file for the XRay class. */

//constructor
var Room = function() {};
Room.prototype = {
    eagerAccessors: [ {method:"loadBuilding", boolean:"Building_id"}],
    BuildingRelationship: {
        className: 	  'Building',
        keyReference:  'Building_id',
        queryString:  'getBuildingById'
    },

    loadBuilding: function(){
        if(!this.Building && this.Buidling_id) {
            dataLoader.loadChildObject( this, 'Building', 'Building', this.Building_id );
        }
    }
}

// inherit from GenericModel
extend(Room, GenericModel);

