angular
    .module("modelInflator", ['genericAPI'])
        .factory("modelInflatorFactory", function modelInflatorFactory( genericAPIFactory, $q, $rootScope, $http, $interval ){

            var inflator = {};
            inflator.$q = $q;

            /**
            *
            *   Converts JSON into a POJO-like object with accessors
            *   @ JSON JSON object containing data which will be
            *   @ Object object    object containing POJO methods that will have JSON data inserted
            *
            **/

            //treat this as a private method, only to be called locally
            inflator.instantiateObjectFromJson = function( json, objectFlavor )
            {

                    if( !objectFlavor ){
                        try {
                            objectFlavor = json.Class;
                        }
                        catch( e ) {
                            alert('no class was found');
                        }
                    }

                    //does this class exist? if not, make it on the fly
                    if( !window[objectFlavor] || objectFlavor == "Response" ) {
                        //if we don't have the class, for now we just return the json
                        return json;
                        console.log("WARNING! Creating Class " + objectFlavor + ' dynamically. THIS SHOULD NOT HAVE TO HAPPEN!');
                        inflator.dynamicallyCreateClass( objectFlavor );
                    }

                    //instantiate an object of Class objectFlavor
                    var modelledObject = new window[objectFlavor]( );
                    modelledObject.setApi( genericAPIFactory );
                    modelledObject.setRootScope( $rootScope );
                    modelledObject.setInflator( this );

                    //modelledObject.setApi( genericAPIFactory );
                    //add all the json's properties to the object
                    angular.extend( modelledObject, json );

                    //create our getters and setters, if we don't already have them
                    //modelledObject = inflator.createEntityAccessors( modelledObject );

                    // call each of this object's eager accessors
                    if(modelledObject.eagerAccessors){
                        var eml = modelledObject.eagerAccessors.length;

                        while(eml--){
                            var em =  modelledObject.eagerAccessors[eml];
                            if(modelledObject[em.boolean])modelledObject[em.method]();
                        }

                    }
                    modelledObject.setPropertiesFromPrototype()

                    return modelledObject;
            }

            //treat this as a public method, called from controller layer
            inflator.instateAllObjectsFromJson = function(  json, objectFlavor  )
            {
                    if ( json instanceof Array ) {
                        var models = [];
                        var i = json.length;
                        while(i--){
                            var currentJsonObj = json[i];
                            //if we have haven't passed a string, get the the class name of the object
                            if( !objectFlavor ) objectFlavor = currentJsonObj.Class;
                            models.push( inflator.instantiateObjectFromJson( currentJsonObj, objectFlavor ) );
                        }

                        return models;

                    } else {
                        //if we have haven't passed a string, get the the class name of the object
                        if( !objectFlavor ) objectFlavor = json.Class;
                        return inflator.instantiateObjectFromJson( json, objectFlavor );
                    }
            }

            //dynamically generate accessors for an object
            inflator.createEntityAccessors = function( object )
            {
                for( var prop in object ){

                    var getterName = 'get' + prop;
                    var setterName = 'set' + prop;

                    if( !window[object.Class].prototype.hasOwnProperty( getterName ) && !prop.match( /(get|set)/ ) ) {
                        inflator.createGetter( getterName, prop, object );
                    }

                    if( !window[object.Class].prototype.hasOwnProperty( setterName ) && !prop.match( /(get|set)/ ) ){
                        inflator.createSetter( setterName, prop, object );
                    }

                    if( object[prop] && object[prop].hasOwnProperty( 'Class' )  && window[prop] &&  !( object[prop] instanceof window[prop] )) inflator.instantiateObjectFromJson( object[prop] );

                }

                return object;
            }

            inflator.createGetter = function( getterName, prop, object ){
                    window[object.Class].prototype[getterName] = function( data ){
                        //lazy load
                        if(data){
                            return data;
                        }
                        if( typeof this[prop+'Relationship'] !== 'undefined' ){
                                var defer = $q.defer();
                                var local = this;
                                var promiseData;

                                //check for the cache for a collection of this object type
                                if( dataStoreManager.getIfExists( this[prop + 'Relationship'].Class) ){

                                    $rootScope[this.Class+"Busy"] = defer.promise;
                                    var foreignKey = this[prop + 'Relationship'].keyReference;
                                    var term       = this[prop + 'Relationship'].Class;
                                    this.Supervisor = dataStoreManager.getChildrenByParentProperty( term, foreignKey, this.Key_id );

                                    return this.Supervisor;
                                    //we return via the object's getterCallback method so that we can wait until the promise is fulfilled
                                    //this way we can display an angular-busy loading directive.

                                }else{
                                    //we don't have this type of object in the cache.  Get a collection from the server
                                    //make a copy of this object so that we don't lose reference to the "this" keyword in the success function
                                    var local = this;

                                    var urlFragment = this[prop + 'Relationship'].queryString;
                                    var queryParam = this[prop + 'Relationship'].queryParam ? this[this[prop + 'Relationship'].queryParam] : '';
                                    //set the $rootScope property for this class equal to the asynch promise so that we can trigger angular-busy
                                    $rootScope[this.Class+"sBusy"] = genericAPIFactory.read( urlFragment, queryParam )
                                        .then(
                                            function( returnedPromise ){
                                                promiseData = returnedPromise.data;
                                            },
                                            function( error ){

                                            }
                                        )

                                        //if anybody can think of a better way to get the data out of the promise above and return it, please, for the love of god, let me know.
                                        var i = 0;
                                        var interval = setInterval(function(){
                                             i++;
                                             hackyClosureExtractor();
                                        },100);


                                        //we run the function repeatedly until the property is set, then we return it.
                                        var hackyClosureExtractor = function(){
                                            if(typeof promiseData != "undefined"){
                                                clearInterval(interval);
                                                this.Supervisor = promiseData;
                                                return this.Supervisor;
                                            }
                                            if(i>100){
                                                clearInterval(interval);
                                                return false;
                                            }

                                        }

                                        return hackyClosureExtractor();
                                }

                        //eager load
                        }else{
                                //is this an entity? if so is it an instance of a Class?
                                if( inflator.needsInstantiation( this, prop ) ){
                                    //instantiate an object of the proper Class so that we have all the methods of GenericModel
                                    var property = new window[this[prop].Class]();
                                    angular.extend( property, this[prop] );
                                    this[prop] = property;
                                }
                                return this[prop];
                        }

                    };

            }

            inflator.createSetter = function( setterName, prop, object)
            {

                    window[object.Class].prototype[setterName] = function( value ){
                            this[prop] = value;
                    };

            }

            //good programmers are lazy. nothing auto generates accessors for JS.  Let's make classes on the fly
            inflator.dynamicallyCreateClass = function( className )
            {
                console.log('dynamically creating '+className+' class');
                //give it a constructor so we can instantiate
                //we may consider passing an optional key_id into all Class constructors so that we can grab objects from the server by passing
                window[className] = function()
                {
                        this.setClass( className );
                }

                window[className].prototype = {

                        getClass:  function()
                        {
                                if( this.hasOwnProperty( 'Class' ) ) return this.Class;
                        },
                        setClass:  function( Class )
                        {
                                this.Class = Class;
                        }

                }


                //inherit from and extend GenericModel
                extend( window[className], GenericModel );
            }

            inflator.needsInstantiation = function( object,  property  )
            {
                    if( window[property] && !(object[property] instanceof window[property]) )  return true;
                    return false;
            }


            return inflator;
        });
