'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var GenericModel = function( api ){
    if(this && api)this.setApi(api);
};

GenericModel.prototype = {
    //pass a reference to the api
    api: {},
    getApi: function()
    {
        return this.api;
    },
    setApi: function( api )
    {
        this.api = api;
    },

    rootScope:{},
    getRootScope: function()
    {
        return this.rootScope;
    },
    setRootScope: function( rootScope )
    {
        this.rootScope = rootScope;
    },

    inflator:{},
    setInflator: function( inflator )
    {
        this.inflator = inflator
    },

    eagerAccessors:[],

    getKey_id: function(){
        return this.Key_id;
    },
    setKey_id:  function( key_id )
    {
        this.Key_id = key_id;
    },

    getDate_created: function()
    {
        if( this.hasOwnProperty( 'Date_created' ) ) return this.Date_created;
    },
    setDate_created: function( Date_created )
    {
        this.Date_created = Date_created;
    },

    getDate_last_modified: function()
    {
        if( this.hasOwnProperty( 'Date_last_modified' ) ) return this.Date_last_modified;
    },
    setDate_last_modified: function( Date_last_modified )
    {
        this.Date_last_modified = Date_last_modified;
    },

    getIs_active: function()
    {
        if( this.hasOwnProperty( 'Is_active' ) ) return this.Is_active;
    },
    setIs_active: function( Is_active )
    {
        this.Is_active = Is_active;
    },

    getLast_modified_user_id: function()
    {
        if( this.hasOwnProperty( 'Last_modified_user_id' ) ) return this.Last_modified_user_id;
    },
    setLast_modified_user_id: function( Last_modified_user_id )
    {
        this.Last_modified_user_id = Last_modified_user_id;
    },

    getCreated_user_id: function()
    {
        if( this.hasOwnProperty( 'Created_user_id' ) ) return this.Created_user_id;
    },
    setCreated_user_id: function( Created_user_id )
    {
        this.Created_user_id = Created_user_id;
    },

    //called when on success when a getter makes an asynch call
    getterCallback:  function( falseFlag )
    {
        if(falseFlag)return false;
        return true;
    },

    setPropertiesFromPrototype: function()
    {

        // if the browser supports it, grab all the prototype properties that this object doesn't already have
        if(typeof Object.getPrototypeOf(this) != "undefined"){
            var proto = Object.getPrototypeOf(this);
            this.loopProto(proto);
        }
        //if that way didn't work, try this one
        else if(typeof this.constructor.prototype != "undefined"){
            var proto = this.constructor.prototype;
            this.loopProto(proto);
        }
        //otherwise, go ahead and set the absolutely essential properties here
        else{
            if(this.Class && !this.hasOwnProperty('Class'))this.Class = this.Class;
        }

    },

    loopProto: function(proto)
    {
        for (var prop in proto) {
          if(!this.hasOwnProperty(prop)){
            this[prop] = proto[prop];
          }
        }
    }

}
GenericModel();
//create an angular module for the model, so it can be injected downstream
angular
    .module("genericModel",[])
    .value("GenericModel",GenericModel);
