////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
//abstract specifies singleton in ts 1.x (ish)
var InstanceFactory = (function (_super) {
    __extends(InstanceFactory, _super);
    function InstanceFactory() {
        _super.apply(this, arguments);
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
                            DataStoreManager.ActualModel[className].Data = [];
                            DataStoreManager.ActualModel[className].getAllPromise = new Promise(function () { });
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
            console.log(className + " not in approved ClassNames, but exists. Trying to create...");
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
            var childStore = DataStoreManager.ActualModel[compMap.ChildType].Data;
            parent[compMap.PropertyName] = []; // clear property
            parent.viewModelWatcher[compMap.PropertyName] = [];
            var len = childStore.length;
            for (var i = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class, DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                    //TODO: ADD view modelWatcher of childstore[i]
                    parent[compMap.PropertyName].push(childStore[i]);
                    parent.viewModelWatcher[compMap.PropertyName].push(childStore[i].viewModelWatcher);
                }
            }
        }
        else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (PermissionMap.getPermission(compMap.ChildType).getAll) {
                if (!DataStoreManager[compMap.ChildType] || !DataStoreManager[compMap.ChildType].getAllCalled || !DataStoreManager[compMap.ChildType].Data) {
                    parent[compMap.PropertyName] = []; // clear property
                    parent.viewModelWatcher[compMap.PropertyName] = [];
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
                                        parent.viewModelWatcher[compMap.PropertyName].push(childStore[i].viewModelWatcher);
                                    }
                                }
                            }
                        })
                            .catch(function (f) {
                            console.log("getChildInstances:", f);
                        });
                    }
                    else {
                        parent[compMap.PropertyName] = []; // clear property
                        parent.viewModelWatcher[compMap.PropertyName] = [];
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
                if (typeof parent[compMap.PropertyName + "Promise"] == "undefined") {
                    var url = parent.getChildUrl(compMap);
                    parent[compMap.PropertyName + "Promise"] = XHR.GET(url).then(function (d) {
                        parent[compMap.PropertyName] = [];
                        parent.viewModelWatcher[compMap.PropertyName] = [];
                        d = InstanceFactory.convertToClasses(d);
                        var len = d.length;
                        for (var i = 0; i < len; i++) {
                            var current = d[i];
                            var existingIndex = _.findIndex(DataStoreManager._actualModel[compMap.ChildType].Data, function (o) { return o.UID == current.UID; });
                            if (existingIndex > -1) {
                                DataStoreManager._actualModel[compMap.ChildType].Data[existingIndex] = current;
                            }
                            else {
                                DataStoreManager._actualModel[compMap.ChildType].Data.push(current);
                            }
                            if (!current.viewModelWatcher) {
                                current.viewModelWatcher = _.cloneDeep(current);
                            }
                            parent[compMap.PropertyName].push(current);
                            parent.viewModelWatcher[compMap.PropertyName].push(current.viewModelWatcher);
                        }
                        return d;
                    });
                }
                else {
                    parent[compMap.PropertyName + "Promise"].then(function (d) {
                        parent[compMap.PropertyName] = [];
                        parent.viewModelWatcher[compMap.PropertyName] = [];
                        d = InstanceFactory.convertToClasses(d);
                        var len = d.length;
                        for (var i = 0; i < len; i++) {
                            var current = d[i];
                            var existingIndex = _.findIndex(DataStoreManager._actualModel[compMap.ChildType].Data, function (o) { return o.UID == current.UID; });
                            if (existingIndex > -1) {
                                DataStoreManager._actualModel[compMap.ChildType].Data[existingIndex] = current;
                            }
                            else {
                                DataStoreManager._actualModel[compMap.ChildType].Data.push(current);
                            }
                            if (!current.viewModelWatcher) {
                                current.viewModelWatcher = _.cloneDeep(current);
                            }
                            parent[compMap.PropertyName].push(current);
                            parent.viewModelWatcher[compMap.PropertyName].push(current.viewModelWatcher);
                        }
                        return d;
                    });
                }
            }
        }
        else {
            // clone collection from actualModel to viewModel
            parent[compMap.PropertyName] = parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
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
}(DataStoreManager));
