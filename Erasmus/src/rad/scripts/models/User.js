'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var User = function( api ){};

User.prototype = {
	//entity relationship mapping

	eagerAccessors:[],

	setEagerAccessors: function( eagerAccessors )
	{
		this.eagerAccessors = eagerAccessors;
	},

	SupervisorRelationship: {

		Class: 	  'PrincipalInvestigator',
		foreignKey:  'Key_id',
		queryString:  'getPIById&id=',
		queryParam:   'Supervisor_id'

	},

	getSupervisor: function()
	{
			console.log('trying to get PI')
			if( dataStoreManager.checkCollection( this.SupervisorRelationship.Class ) ){
                    console.log('trying to find cached PI');
                    //var defer = $q.defer();     
                    //this.rootScope[this.Class+"Busy"] = defer.promise;
                    var foreignKey = this[this.SupervisorRelationship.keyReference];
                    var term       = this.SupervisorRelationship.Class+'s';
                    this.Supervisor = dataStoreManager.getById( "PrincipalInvestigator", this.Supervisor_id );

                   // return this.Supervisor;
                    //we return via the object's getterCallback method so that we can wait until the promise is fulfilled
                    //this way we can display an angular-busy loading directive.
                                                                 
            }
            else if(this.Supervisor){
            		return this.Supervisor;
            }
            else{
            		console.log("searching server for pi");
            		var local = this;

                    var urlFragment = this.SupervisorRelationship.queryString;
                    var queryParam = this[this.SupervisorRelationship.queryParam];
                    var promiseData;

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
	}
}


//inherit from and extend GenericModel
extend(User, GenericModel);

User();


//create an angular module for the model, so it can be injected downstream

var usermodule = angular
	.module("User",[])
	.value("User",User)

