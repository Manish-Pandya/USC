'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    eagerAccessors: [
        {method:"loadUser", boolean:"User_id"},
        {method:"loadCarboys", boolean:true},
        {method:"loadSolidsContainers", boolean:true},
        {method:"loadWasteBags", boolean:"SolidsContainers"},
        {method:"loadRooms", boolean:true},
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
        table: 	  'principal_investigator_room',
        childClass: 'Room',
        parentProperty: 'Rooms',
        isMaster: true
    },

    AuthorizationsRelationship: {
        className:    'Authorization',
        keyReference:  'Principal_investigator_id',
        methodString:  'getAuthorizationsByPIId',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    ActiveParcelsRelationship: {
        className:    'Parcel',
        keyReference:  'Principal_investigator_id',
        methodString:  'getActiveParcelsFromPIById',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    PurchaseOrdersRelationship: {
        className:    'PurchaseOrder',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    SolidsContainersRelationship: {
        className:    'SolidsContainer',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    CurrentScintVialCollectionRelationship: {
        className:    'ScintVialCollection',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id',
        where:[{'Pickup_id':"IS NULL"}]
    },


    CarboyUseCyclesRelationship: {
        className:    'CarboyUseCycle',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    PickupsRelationship: {
        className:    'Pickup',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    Pi_AuthorizationsRelationship: {
        className:    'PIAuthorization',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },


    Buildings: {},

    loadActiveParcels: function() {
        dataLoader.loadOneToManyRelationship( this, 'ActiveParcels', this.ActiveParcelsRelationship, null, true  );
    },

    loadRooms: function() {
        dataLoader.loadManyToManyRelationship( this, this.RoomsRelationship );
    },

    loadPurchaseOrders: function() {
        dataLoader.loadOneToManyRelationship( this, 'PurchaseOrders', this.PurchaseOrdersRelationship);
    },

    loadSolidsContainers: function() {
        dataLoader.loadOneToManyRelationship( this, 'SolidsContainers', this.SolidsContainersRelationship);
    },

    loadCarboyUseCycles: function() {
        dataLoader.loadOneToManyRelationship( this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship);
    },

    loadPickups: function() {
        dataLoader.loadOneToManyRelationship( this, 'Pickups', this.PickupsRelationship);
    },

    loadPIAuthorizations: function() {
        dataLoader.loadChildObjectByParentProperty(this, "Pi_authorization", "PIAuthorization", this.Key_id, "Principal_investigator_id");
    },

    loadUser:  function() {
        if(!this.User && this.User_id) {
            dataLoader.loadChildObject( this, 'User', 'User', this.User_id );
        }
    },

    loadWasteBags: function() {
        if(!this.WasteBags && this.SolidsContainers){
            this.WasteBags = [];
            var i = this.SolidsContainers.length;
            while(i--){
                if(this.SolidsContainers[i].CurrentWasteBags && this.SolidsContainers[i].CurrentWasteBags.length){
                    this.SolidsContainers[i].CurrentWasteBags[0] = this.inflator.instateAllObjectsFromJson(this.SolidsContainers[i].CurrentWasteBags[0]);
                    this.WasteBags.push(this.SolidsContainers[i].CurrentWasteBags[0]);
                }
            }
        }
    },

    loadCurrentScintVialCollections: function () {
        this.CurrentScintVialCollections = [];
        dataLoader.loadOneToManyRelationship( this, 'CurrentScintVialCollections', this.CurrentScintVialCollectionRelationship);
    }

}


//inherit from and extend GenericPrincipalInvestigator
extend(PrincipalInvestigator, GenericPrincipalInvestigator);

//create an angular module for the model, so it can be injected downstream
angular
    .module("principalInvestigator",[])
    .value("PrincipalInvestigator",PrincipalInvestigator);
