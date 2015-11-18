'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var PIAuthorization = function() {};
PIAuthorization.prototype = {
    className: "PIAuthorization",
    Class: "PIAuthorization",

    AuthorizationsRelationship: {
        className:    'Authorization',
        keyReference:  'Pi_authorization_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    eagerAccessors: [{method:"loadAuthorizations", boolean: "Key_id"}],

    instantiateAuthorizations: function(){
        this.Authorizations = this.inflator.instateAllObjectsFromJson(this.Authorizations);
    },

    loadAuthorizations: function() {
        dataLoader.loadOneToManyRelationship(this, "Authorizations", this.AuthorizationsRelationship);
    },
}

// inherit from GenericModel
extend(PIAuthorization, GenericModel);
