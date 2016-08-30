////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
//abstract specifies singleton in ts 1.x (ish)
var InstanceFactory = (function () {
    function InstanceFactory() {
    }
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    // Gets array of class names from script tags with src containing the provided basePath.
    InstanceFactory.getClassNames = function (basePath) {
        if (basePath === void 0) { basePath = ""; }
        if (!this._classNames) {
            this._classNames = [];
            var scripts = document.getElementsByTagName('script');
            if (scripts && scripts.length > 0) {
                for (var i in scripts) {
                    if (scripts[i].src && scripts[i].src.indexOf(basePath) > -1) {
                        var pathArray = scripts[i].src.split("/");
                        var className = pathArray.pop().split(".")[0];
                        if (this._classNames.indexOf(className) == -1) {
                            this._classNames.push(className);
                            //init DataStoreManager holders
                            DataStoreManager.ActualModel[className] = {};
                            DataStoreManager.ActualModel[className].getAllPromise = new Promise(function () { }, function () { });
                            console.log(DataStoreManager.ActualModel[className]);
                        }
                    }
                }
            }
        }
        return this._classNames;
    };
    // Creates class instance if possible
    InstanceFactory.createInstance = function (className) {
        if (this._classNames && this._classNames.indexOf(className) > -1) {
            return new window[className]();
        }
        else if (window[className]) {
            console.log("Class not in approved ClassNames, but exists. Trying to create...");
            return new window[className]();
        }
        else {
            //console.log("No such class as " + className);
            return null;
        }
    };
    // Crawls through data and its children, creating class instances as needed.
    InstanceFactory.convertToClasses = function (data) {
        var _this = this;
        if (data && data[0] && data[0][DataStoreManager.classPropName]) {
            var instance = InstanceFactory.createInstance(data[0].Class);
            InstanceFactory.copyProperties(instance, data[0]);
            instance.onFulfill();
            for (var instanceProp in instance) {
                if (instance[instanceProp] instanceof CompositionMapping) {
                    //console.log("dig:", instanceProp);
                    console.log("the scoped CompositionMapping should be", compMap);
                    var compMap = instance[instanceProp];
                    // Do it here
                    if (compMap.callGetAll) {
                        //console.log("oh shit, boi wattup");
                        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
                            //TODO:  store this promise somewhere way up in scope
                            /*Promise.all([DataStoreManager.getAll(compMap.ChildType, []), compMap]).then((d) => {
                                this.getChildInstances(d[1], instance);
                            })*/
                            //TODO:  SECOND INDEX is a reference to compMap in the parent scope on the correct index of this loop, but we are insane for passing it this way
                            Promise.all([DataStoreManager.getAll(compMap.ChildType, []), compMap]).then(function (d) {
                                _this.getChildInstances(d[1], instance);
                            });
                        }
                        else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
                            var getGerunds = XHR.GET(compMap.ChildUrl);
                            Promise.all([getGerunds, DataStoreManager.getAll(compMap.ChildType, []), compMap]).then(function (d) {
                                //console.log(DataStoreManager.ActualModel)
                                console.log("the scoped comp map in many to many is", d[2]);
                                _this.getChildInstances(d[2], instance);
                            });
                        }
                    }
                }
            }
        }
        var drillDown = function (parentNode) {
            for (var prop in parentNode) {
                if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                    if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                        var instance = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                        if (instance) {
                            instance = InstanceFactory.copyProperties(instance, parentNode[prop]);
                            instance.onFulfill();
                            // Run composition routine here based on instance's CompositionMapping //
                            // set instance
                            parentNode[prop] = instance;
                        }
                    }
                    drillDown(parentNode[prop]);
                }
            }
        };
        drillDown(data);
        return data;
    };
    InstanceFactory.getChildInstances = function (compMap, parent) {
        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
            if (!parent[compMap.PropertyName])
                parent[compMap.PropertyName] = [];
            var len = DataStoreManager.ActualModel[compMap.ChildType].Data.length;
            for (var i = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class,DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                    //perhaps use a DataStore manager method that leverages findByPropValue here
                    parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                }
            }
        }
        else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            console.log(parent.Key_id);
            if (!DataStoreManager[compMap.ChildType] || !DataStoreManager[compMap.ChildType].getAllCalled || !DataStoreManager[compMap.ChildType].Data) {
                if (!parent[compMap.PropertyName])
                    parent[compMap.PropertyName] = [];
                //Get the gerunds.then
                var manyTypeToManyChildType = parent.TypeName + "To" + compMap.ChildType;
                if (typeof DataStoreManager.ActualModel[manyTypeToManyChildType] == "undefined" || !DataStoreManager.ActualModel[manyTypeToManyChildType].promise) {
                    DataStoreManager.ActualModel[manyTypeToManyChildType] = {};
                    DataStoreManager.ActualModel[manyTypeToManyChildType].promise = XHR.GET(compMap.GerundUrl)
                        .then(function (d) {
                        DataStoreManager.ActualModel[manyTypeToManyChildType].stuff = d;
                        //find relevant gerunds for this parent instance
                    })
                        .catch(function (f) {
                        console.log(f);
                    });
                }
                else {
                    DataStoreManager.ActualModel[manyTypeToManyChildType].promise.then(function (d) {
                        //find relevant gerunds for this parent instance
                    });
                }
                parent["test"] = "l;aksjfl;akjsdlf";
                if (DataStoreManager.ActualModel[manyTypeToManyChildType].stuff) {
                    var len = DataStoreManager.ActualModel[manyTypeToManyChildType].stuff.length;
                    console.log(parent.Key_id);
                    return;
                    for (var i = 0; i < len; i++) {
                        if (DataStoreManager.ActualModel[manyTypeToManyChildType].stuff[i][compMap.DEFAULT_MANY_TO_MANY_PARENT_ID] == parent.UID) {
                            compMap.LinkingMaps.push(DataStoreManager.ActualModel[manyTypeToManyChildType].stuff[i]);
                        }
                        console.log(parent.Class, parent.UID, compMap.LinkingMaps);
                    }
                }
            }
        }
        else {
            var childInstance = this.createInstance(compMap.ChildType);
            childInstance.Email = "foo@yoo.poo";
        }
    };
    // Copies properties/values from sources to target.
    // It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy test pending."
    InstanceFactory.copyProperties = function (target) {
        var sources = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            sources[_i - 1] = arguments[_i];
        }
        sources.forEach(function (source) {
            Object.defineProperties(target, Object.getOwnPropertyNames(source).reduce(function (descriptors, key) {
                descriptors[key] = Object.getOwnPropertyDescriptor(source, key);
                /*
                PUMP IN OBSERVERS AT GRANULAR LEVEL HERE BECAUSE IT WILL BE PERFORMANT EVEN IF DAVID DOESN'T THINK WE NEED IT TO BE
                if (!actualModelThing.flatWatcherMap.indexOf(viewModelThing.uid)) {
                        actualModelThing.
                        actualModelThing.Watchers.push();
                }
                */
                return descriptors;
            }, {}));
        });
        return target;
    };
    return InstanceFactory;
}());
