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
                            DataStoreManager.ActualModel[className].getAllPromise = function () { }; //new Promise(function () { }, function () { });
                            DataStoreManager.ActualModel[className].getByIdPromise = new Promise(function () { }, function () { });
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
        if (data && data[DataStoreManager.classPropName]) {
            var instance = InstanceFactory.createInstance(data[DataStoreManager.classPropName]);
            InstanceFactory.copyProperties(instance, data);
            instance.onFulfill();
            return instance;
        }
        var drillDown = function (parentNode) {
            for (var prop in parentNode) {
                if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                    if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                        var instance = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                        if (instance) {
                            instance = InstanceFactory.copyProperties(instance, parentNode[prop]);
                            parentNode[prop] = instance; // set instance
                            instance.onFulfill();
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
            if (!parent[compMap.PropertyName] || parent[compMap.PropertyName] == null)
                parent[compMap.PropertyName] = [];
            var len = DataStoreManager.ActualModel[compMap.ChildType].Data.length;
            for (var i = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class, DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                    parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                }
            }
            // init collection in viewModel to be replaced with referenceless actualModel data
            parent.viewModelWatcher[compMap.PropertyName] = [];
            // clone collection from actualModel to viewModel
            parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
        }
        else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (!DataStoreManager[compMap.ChildType] || !DataStoreManager[compMap.ChildType].getAllCalled || !DataStoreManager[compMap.ChildType].Data) {
                if (!parent[compMap.PropertyName] || parent[compMap.PropertyName] == null)
                    parent[compMap.PropertyName] = [];
                //Get the gerunds.then
                var manyTypeToManyChildType = parent.TypeName + "To" + compMap.ChildType;
                if (typeof DataStoreManager.ActualModel[manyTypeToManyChildType] == "undefined" || !DataStoreManager.ActualModel[manyTypeToManyChildType].promise) {
                    DataStoreManager.ActualModel[manyTypeToManyChildType] = {};
                    DataStoreManager.ActualModel[manyTypeToManyChildType].promise = XHR.GET(compMap.GerundUrl)
                        .then(function (d) {
                        DataStoreManager.ActualModel[manyTypeToManyChildType].Data = d;
                        console.log(parent.Class, compMap.ChildType);
                        var childStore = DataStoreManager.ActualModel[compMap.ChildType].Data;
                        var gerundLen = d.length;
                        //loop through all the gerunds
                        for (var i = 0; i < gerundLen; i++) {
                            var g = d[i];
                            var childLen = childStore.length;
                            for (var x = 0; x < childLen; x++) {
                                if (parent.UID == g.ParentId && childStore[x].UID == g.ChildId) {
                                    parent[compMap.PropertyName].push(childStore[x]);
                                }
                            }
                        }
                        // init collection in viewModel to be replaced with referenceless actualModel data
                        parent.viewModelWatcher[compMap.PropertyName] = [];
                        // clone collection from actualModel to viewModel
                        parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
                    })
                        .catch(function (f) {
                        console.log("getChildInstances:", f);
                    });
                }
                else {
                    DataStoreManager.ActualModel[manyTypeToManyChildType].promise.then(function (d) {
                        var childStore = DataStoreManager.ActualModel[compMap.ChildType].Data;
                        var d = DataStoreManager.ActualModel[manyTypeToManyChildType].Data;
                        var gerundLen = d.length;
                        //loop through all the gerunds
                        for (var i = 0; i < gerundLen; i++) {
                            var g = d[i];
                            var childLen = childStore.length;
                            for (var x = 0; x < childLen; x++) {
                                var child = childStore[x];
                                if (child.UID == g.ChildId && parent.UID == g.ParentId) {
                                    parent[compMap.PropertyName].push(child);
                                }
                            }
                        }
                        // init collection in viewModel to be replaced with referenceless actualModel data
                        parent.viewModelWatcher[compMap.PropertyName] = [];
                        // clone collection from actualModel to viewModel
                        parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
                    });
                }
                return;
            }
        }
        else {
            // clone collection from actualModel to viewModel
            parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
        }
    };
    // Copies properties/values from sources to target.
    // It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy NOT working."
    InstanceFactory.copyProperties = function (target) {
        var sources = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            sources[_i - 1] = arguments[_i];
        }
        sources.forEach(function (source) {
            Object.defineProperties(target, Object.getOwnPropertyNames(source).reduce(function (descriptors, key) {
                descriptors[key] = Object.getOwnPropertyDescriptor(source, key);
                return descriptors;
            }, {}));
        });
        return target;
    };
    return InstanceFactory;
}());
