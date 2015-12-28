'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    eagerAccessors: [
        {method:"loadUser", boolean:"User_id"},
    ],

    UserRelationship: {
        className: 	  'User',
        keyReference:  'User_id',
        queryString:  'getUserById',
        queryParam:   ''
    },


    loadUser:  function() {
        if(!this.User && this.User_id) {
            dataLoader.loadChildObject( this, 'User', 'User', this.User_id );
        }
    }

}

//inherit from and extend GenericPrincipalInvestigator
extend(PrincipalInvestigator, GenericModel);
