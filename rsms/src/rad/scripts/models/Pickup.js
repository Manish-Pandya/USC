'use strict';
/* Auto-generated stub file for the Pickup class. */

//constructor
var Pickup = function() {};
Pickup.prototype = {
	className: "Pickup",

	eagerAccessors:[
		{method:"loadPrincipalInvestigator", boolean:"Principal_investigator_id"},
	],


	CurrentScintVialCollectionRelationship: {
	    className: 'ScintVialCollection',
	    keyReference: 'Pickup_id',
	    methodString: '',
	    paramValue: 'Key_id',
	    paramName: 'id'
	},

	CarboyUseCyclesRelationship: {
	    className: 'CarboyUseCycle',
	    keyReference: 'Pickup_id',
	    methodString: '',
	    paramValue: 'Key_id',
	    paramName: 'id'
	},

	WasteBagRelationship: {
	    className: 'WasteBag',
	    keyReference: 'Pickup_id',
	    methodString: '',
	    paramValue: 'Key_id',
	    paramName: 'id'
	},

	loadPrincipalInvestigator: function()
	{
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject(this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id);
        }
	},

	loadCarboyUseCycles: function () {
	    dataLoader.loadOneToManyRelationship(this, 'Carboy_use_cycles', this.CarboyUseCyclesRelationship);
	},

	loadWasteBags: function () {
	    this.WasteBags = [];
	    dataLoader.loadOneToManyRelationship(this, 'Waste_bags', this.WasteBagRelationship);
	    dataLoader.loadOneToManyRelationship(this, 'Waste_bagssss', this.WasteBagRelationship);

	},

	loadCurrentScintVialCollections: function () {
	    this.CurrentScintVialCollections = [];
	    dataLoader.loadOneToManyRelationship(this, 'Scint_vial_collections', this.CurrentScintVialCollectionRelationship);
	}

}

// inherit from GenericModel
extend(Pickup, GenericModel);