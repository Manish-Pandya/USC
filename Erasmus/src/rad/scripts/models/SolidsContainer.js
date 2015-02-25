'use strict';
/* Auto-generated stub file for the SolidsContainer class. */

//constructor
var SolidsContainer = function() {};
SolidsContainer.prototype = {

	loadRoom: function(){
		if(!this.Room && this.Room_id) {
            dataLoader.loadChildObject( this, 'Room', 'Room', this.Room_id );
        }
	}

}

// inherit from GenericModel
extend(SolidsContainer, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("solidscontainer", [])
    .value("SolidsContainer", SolidsContainer);

