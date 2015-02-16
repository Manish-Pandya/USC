'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var User = function( api ){};

User.prototype = {




/*
	getName: function(){
		if( this.hasOwnProperty( 'Name' ) ) return this.lazyLoad( Name );
	},
	setName:  function( Name )
	{
		this.Name = Name;
	},

	getRoles:  function()
	{
		if( this.hasOwnProperty( 'Roles' ) ) return this.Roles;
	},
	setRoles:  function( Roles )
	{
		this.Roles = Roles;
	},

	//figure our relationships
	getPrincipalInvestigator: function()
	{
		if( this.hasOwnProperty( 'PrincipalInvestigator' ) ) return this.PrincipalInvestigator;
	},
	setPrincipalInvestigator:  function( PrincipalInvestigator )
	{
		this.PrincipalInvestigator = PrincipalInvestigator;
	},

	getInspector:  function()
	{
		if( this.hasOwnProperty( 'Inspector' ) ) return this.Inspector;
	},
	setInspector:  function(Inspector)
	{
		this.Inspector = Inspector;
	},

	getSupervisor_id:  function()
	{
		if( this.hasOwnProperty( 'Supervisor_id' ) ) return this.Supervisor_id;
	},
	setSupervisor_id:  function( Supervisor_id )
	{
		this.Supervisor_id = Supervisor_id;
	},

	getSupervisor:  function()
	{
		if( this.hasOwnProperty( 'Supervisor' ) ) return this.Supervisor;
	},
	setSupervisor:  function( Supervisor )
	{
		this.Supervisor = Supervisor;
	},

	getUsername:  function()
	{
		if( this.hasOwnProperty( 'Username' ) ) return this.Username;
	},
	setUsername:  function( Username )
	{
		this.Username = Username;
	},

	getFirst_name:  function( First_name )
	{
		if( this.hasOwnProperty( 'First_name' ) ) return this.First_name;
	},
	setFirst_name:  function( First_name )
	{
		this.First_name = First_name;
	},

	getLast_name:  function( Last_name )
	{
		if( this.hasOwnProperty( 'Last_name' ) ) return this.Last_name;
	},
	setLast_name:  function( Last_name )
	{
		this.Last_name = Last_name;
	},	

	getEmail:  function( getEmail )
	{
		if( this.hasOwnProperty( 'Email' ) ) return this.Email;
	},
	setEmail:  function( Email )
	{
		this.Email = Email;
	},

	getEmergency_phone:  function()
	{
		if( this.hasOwnProperty( 'Emergency_phone' ) ) return this.Emergency_phone;
	},
	setEmergency_phone:  function( Emergency_phone )
	{
		this.Emergency_phone = Emergency_phone;
	},

	getLab_phone:  function()
	{
		if( this.hasOwnProperty( 'Lab_phone' ) ) return this.Lab_phone;
	},
	setLab_phone:  function( Lab_phone )
	{
		this.Lab_phone = Lab_phone;
	},

	getOffice_phone:  function()
	{
		if( this.hasOwnProperty( 'Office_phone' ) ) return this.Office_phone;
	},
	setOffice_phone:  function( Office_phone )
	{
		this.Office_phone = Office_phone;
	},

	getPrimary_department_id:  function()
	{
		if( this.hasOwnProperty( 'Primary_department_id' ) ) return this.Primary_department_id;
	},
	setPrimary_department_id:  function( Primary_department_id )
	{
		this.Primary_department_id = Primary_department_id;
	},

	getPosition:  function()
	{
		if( this.hasOwnProperty( 'Position' ) ) return this.Position;
	},
	setPosition:  function( Position )
	{
		this.Position = Position;
	},

	getPrimary_department:  function()
	{
		if( this.hasOwnProperty( 'Primary_department' ) ) return this.Primary_department;
	},
	setPrimary_department:  function( Primary_department )
	{
		this.Primary_department = Primary_department;
	},
	*/
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

                        //if anybody can think of a better way to get the data out of the promise above and return it, please, for the love of god, let me know.
                       	/*
                        var i = 0;
                        var interval = setInterval(function(){          
                             i++;
                             console.log(i);
                             hackyClosureExtractor();
                        },100);

                        
                        //we run the function repeatedly until the property is set, then we return it.
                        var hackyClosureExtractor = function(){
                            if(typeof promiseData != "undefined"){
                                clearInterval(interval);
                                local.Supervisor = promiseData;
                                console.log(local.Supervisor);
                                local.rootScope.$apply();
                                return local.Supervisor;
                            }
                            if(i>100){
                                clearInterval(interval);
                                return false; 
                            }

                        }

                        return hackyClosureExtractor();
                        */
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

