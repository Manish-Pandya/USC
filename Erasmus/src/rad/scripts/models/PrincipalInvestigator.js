'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {

	eagerAccessors:[
		{method:"getPrincipalInvestigatorRoomRelations"},
		{method:"getUser"},
		{method:"getLabPersonnel"},
		{method:"getRooms", bolean:"PrincipalInvestigatorRoomRelations"}
	],

	UserRelationship: {

		className: 	  'User',
		keyReference:  'User_id',
		queryString:  'getUserById',
		queryParam:   ''	

	},

	LabPersonnelRelationship: {
		className: 	  'User',
		keyReference:  'Supervisor_id',
		queryString:  'getUserById',
		queryParam:  ''
	},

	PrincipalInvestigatorRoomRelationRelationship: {
		Class: 	  'PrincipalInvestigatorRoomRelation',
		foreignKey:  'Principal_investigator_id',
		queryString:  'getPrincipalInvestigatorRoomRelationsByPiId&id=',
		queryParam:   'Key_id'
	},	

	RoomsRelationship:{
		className: 	  'Room',
		keyReference: 'principalInvestigator_id',
		queryString:  'getRooms'	
	},

	Buildings: {},

	getUser: function()
	{	
		//alert('getting user');
		if( dataStoreManager.checkCollection( 'Users' ) ){
                    //console.log('trying to find cached relations');
                    //var defer = $q.defer();     
                    //this.rootScope[this.Class+"Busy"] = defer.promise;
                    this.User = dataStoreManager.getById( 'User', this.User_id );

                   // return this.Supervisor;
                    //we return via the object's getterCallback method so that we can wait until the promise is fulfilled
                    //this way we can display an angular-busy loading directive.
                                                                 
            }	
	},
	getBuildings: function(rooms)
	{
		if(!this.rooms && !rooms)return false;
		if(!this.rooms)this.rooms = rooms;
		var roomLen
	},

	getPrincipalInvestigatorRoomRelations: function()
	{
			if( dataStoreManager.checkCollection( 'PrincipalInvestigatorRoomRelations' ) ){                    
                    this.PrincipalInvestigatorRoomRelations = dataStoreManager.getChildrenByParentProperty( 'PrincipalInvestigatorRoomRelation', 'Principal_investigator_id', this.Key_id );                                                                 
            }
            else if(this.Supervisor){
            		return this.PrincipalInvestigatorRoomRelations;
            }
            else{
            		var local = this;

                    var urlFragment = this.PrincipalInvestigatorRoomRelationRelationship.queryString;
                    var queryParam = this[this.PrincipalInvestigatorRoomRelationRelationship.queryParam];

                    this.rootScope[this.Class+"sBusy"] = this.api.read( urlFragment, queryParam )
                        .then(
                            function( returnedPromise ){
                                local.PrincipalInvestigatorRoomRelations = local.inflator.instateAllObjectsFromJson( returnedPromise.data );
                            },
                            function( error ){

                            }
                        )
            }
	},

	getRooms: function()
	{
			if(!this.PrincipalInvestigatorRoomRelations)this.getPrincipalInvestigatorRoomRelations();

			if( dataStoreManager.checkCollection( "Rooms" ) ){
                   this.Rooms = dataStoreManager.getRelatedItems( "Room", this.PrincipalInvestigatorRoomRelations, "Room_id", "Key_id" )                                                                 
            }
            else if(this.Supervisor){
            		return this.Supervisor;
            }
            else{
            		console.log("searching server for rooms");
            		var local = this;

                    var urlFragment = this.SupervisorRelationship.queryString;
                    var queryParam = this[this.SupervisorRelationship.queryParam];

                    //set the rootScope property for this class equal to the asynch promise so that we can trigger angular-busy
                    this.rootScope[this.Class+"sBusy"] = this.api.read( urlFragment, queryParam )
                        .then(
                            function( returnedPromise ){
                                local.Supervisor = local.inflator.instateAllObjectsFromJson( returnedPromise.data );
                                console.log( local.Supervisor );
                            },
                            function( error ){

                            }
                        )
            }
	},

	getLabPersonnel: function()
	{
			console.log('getting user');
			if( dataStoreManager.checkCollection( 'Users' ) ){
					this.LabPersonnel=[];
                    //console.log('trying to find cached relations');
                    //var defer = $q.defer();     
                    //this.rootScope[this.Class+"Busy"] = defer.promise;
                    this.LabPersonnel = dataStoreManager.getChildrenByParentProperty( 'User', "Supervisor_id", this.Key_id, this.Class );

                   // return this.Supervisor;
                    //we return via the object's getterCallback method so that we can wait until the promise is fulfilled
                    //this way we can display an angular-busy loading directive.
                                                                 
            }
	}

}

//inherit from and extend GenericModel
extend(PrincipalInvestigator, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("principalInvestigator",[])
	.value("PrincipalInvestigator",PrincipalInvestigator);

