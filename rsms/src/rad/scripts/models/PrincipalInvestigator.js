'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function () {
    PrincipalInvestigator.url = "";
    PrincipalInvestigator.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
};

PrincipalInvestigator.prototype = {
    eagerAccessors: [
        {method:"loadUser", boolean:"User_id"},
        {method:"loadCarboys", boolean:true},
        {method:"loadWasteBags", boolean:true},
        { method: "loadRooms", boolean: true },
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

    WasteBagsRelationship: {
        className:    'WasteBag',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    CurrentWasteBagRelationship: {
        className: 'WasteBag',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id',
        where: [{ 'Pickup_id': "IS NULL" }]
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

    WipeTestsRelationship: {
        className: 'PIWipeTest',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    PIAuthorizationsRelationship: {
        className: 'PIAuthorization',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },


    Buildings: {},

    loadActiveParcels: function () {
        return dataLoader.loadOneToManyRelationship( this, 'ActiveParcels', this.ActiveParcelsRelationship, null, true  );
    },

    loadRooms: function() {
        return dataLoader.loadManyToManyRelationship(this, this.RoomsRelationship);
    },

    loadPurchaseOrders: function() {
        return dataLoader.loadOneToManyRelationship(this, 'PurchaseOrders', this.PurchaseOrdersRelationship);
    },

    loadSolidsContainers: function() {
        return dataLoader.loadOneToManyRelationship(this, 'SolidsContainers', this.SolidsContainersRelationship);
    },

    loadCarboyUseCycles: function () {
        return dataLoader.loadOneToManyRelationship(this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship);
    },

    loadPickups: function() {
        return dataLoader.loadOneToManyRelationship(this, 'Pickups', this.PickupsRelationship);
    },

    loadPIAuthorizations: function() {
        return dataLoader.loadOneToManyRelationship(this, 'Pi_authorization', this.PIAuthorizationsRelationship);
    },

    loadPIWipeTests: function () {
        return dataLoader.loadOneToManyRelationship(this, 'WipeTests', this.WipeTestsRelationship);
    },

    loadUser:  function() {
        if (!this.User && this.User_id) {
            dataLoader.loadChildObject(this, 'User', 'User', this.User_id);
        }
    },

    loadWasteBags: function () {
        return dataLoader.loadOneToManyRelationship(this, 'WasteBags', this.WasteBagsRelationship);
    },

    loadCurrentWasteBag: function () {
        //return dataLoader.loadOneToManyRelationship(this, 'CurrentWasteBag', this.CurrentWasteBagRelationship);
    },

    loadCurrentScintVialCollections: function () {
        // this.CurrentScintVialCollections = [];
        console.log(this.CurrentScintVialCollections);
        alert("called");
        dataLoader.loadOneToManyRelationship( this, 'CurrentScintVialCollections', this.CurrentScintVialCollectionRelationship);
    },
    getName: function () {
        this.Name = "";

        if (this.User) this.Name = this.User.Name;
        console.log(this.Name);
        return this.Name;
    }

}


//inherit from and extend GenericPrincipalInvestigator
extend(PrincipalInvestigator, GenericPrincipalInvestigator);

PrincipalInvestigator();

//create an angular module for the model, so it can be injected downstream
angular
    .module("principalInvestigator",[])
    .value("PrincipalInvestigator",PrincipalInvestigator);
