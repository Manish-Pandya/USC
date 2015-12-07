'use strict';
/* Auto-generated stub file for the XRay class. */

//constructor
var Room = function() {};
Room.prototype = {
   // eagerAccessors: [ {method:"loadBuilding", boolean:"Building_id"}],

    loadBuilding: function(){
        alert('asdf')
        if(!this.Building) {
            dataLoader.loadChildObject( this, 'Building', 'Building', this.Building_id );
        }
    }
}

// inherit from GenericModel
extend(Room, GenericModel);

