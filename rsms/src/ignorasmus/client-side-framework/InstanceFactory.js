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
        var drillDown = function (parentNode) {
            for (var prop in parentNode) {
                if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                    if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                        var instance = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                        if (instance) {
                            instance = InstanceFactory.copyProperties(instance, parentNode[prop]);
                            instance.onFulfill();
                            // Run composition routine here based on instance's CompositionMapping //
                            for (var instanceProp in instance) {
                                if (instance[instanceProp] instanceof CompositionMapping) {
                                    var compMap = instance[instanceProp];
                                    _this.getChildInstances(compMap, instance);
                                }
                            }
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
            //TODO:  wrap in promise if IsPromisified
            if (!parent[compMap.PropertyName])
                parent[compMap.PropertyName] = [];
            if (DataStoreManager.ActualModel[compMap.ChildType].getAllPromise) {
                DataStoreManager.getAll(compMap.ChildType, parent[compMap.PropertyName]).then(function () {
                    var len = DataStoreManager.ActualModel[compMap.ChildType].Data.length;
                    for (var i = 0; i < len; i++) {
                        //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                        if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                            //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class,DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                            //perhaps use a DataStore manager method that leverages findByPropValue here
                            parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                        }
                    }
                });
            }
        }
        else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            //console.log(parent.TypeName,DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType]);
            if (!parent[compMap.PropertyName])
                parent[compMap.PropertyName] = [];
            //Get the gerunds.then
            if (typeof DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType] == "undefined" || !DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise) {
                DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType] = {};
                DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise = $.getJSON(DataStoreManager.baseUrl + compMap.GerundUrl)
                    .done(function (d) {
                    DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].stuff = d;
                    //find relevant gerunds for this parent instance
                });
            }
            else {
                DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise.then(function (d) {
                    //find relevant gerunds for this parent instance
                });
            }
            //, DataStoreManager.ActualModel[compMap.ChildType].getAllPromise NEVER GETS FULFILLED!?!?!?!?!?
            Promise.all([DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise, DataStoreManager.getAll(compMap.ChildType, parent[compMap.PropertyName])])
                .then(function (data) {
                for (var i = 0; i < DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].stuff.length; i++) {
                    if (DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].stuff[i][compMap.DEFAULT_MANY_TO_MANY_PARENT_ID] == parent.UID) {
                        compMap.LinkingMaps.push(DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].stuff[i]);
                        parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                        console.log('dig');
                    }
                    if (parent.Key_id == 1) {
                        console.log(parent, DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].stuff[i][compMap.DEFAULT_MANY_TO_MANY_PARENT_ID], parent.UID);
                    }
                }
            });
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
