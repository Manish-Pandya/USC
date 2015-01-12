'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function(){};
Hazard.prototype = {

	SubHazardsRelationship: {

		Class: 	  'Hazard',
		keyReference:  'Parent_hazard_id',
		queryString:  'getHazardTreeNode'	

	},

	SaveUrl:  'saveHazard',

	getSubHazards: function()
	{
			if( dataStoreManager.checkCollection( 'Hazards' ) ){                    
                    this.SubHazards = dataStoreManager.getChildrenByParentProperty( 'Hazard', 'Parent_hazard_id', this.Key_id );                                                                 
            }
            else if(this.SubHazards){
            		return this.SubHazards;
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

}

//inherit from and extend GenericModel
extend(Hazard, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("hazard",[])
	.value("Hazard",Hazard);

