'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    eagerAccessors: [{method:"loadRooms",boolean:"Key_id" }],
    
    RoomsRelationship:{
        table: 	  'principal_investigator_room',
        childClass: 'Room',
        parentProperty: 'Rooms',
        isMaster: true
    },


    Buildings: {},

    loadRooms: function() {
        dataLoader.loadManyToManyRelationship( this, this.RoomsRelationship);
    },
    
    loadBuildings: function(){
        this.Buildings = [];
        var i = this.Rooms.length;
        while(i--){
            if(this.Buildings.indexOf(this.Rooms[i].Building.Name)){
                this.Buildings.push(this.Rooms[i].Building.Name);
            }
        }
    }

}


//inherit from and extend GenericPrincipalInvestigator
extend(PrincipalInvestigator, GenericModel);