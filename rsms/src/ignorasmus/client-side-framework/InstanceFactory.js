////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
//abstract specifies singleton in ts 1.x (ish)
var InstanceFactory = (function (_super) {
    __extends(InstanceFactory, _super);
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    function InstanceFactory() {
        return _super.call(this) || this;
    } // Static class cannot be instantiated
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    /**
     * Gets array of class names from script tags with src containing the provided basePath.
     *
     * @param basePath
     */
    InstanceFactory.getClassNames = function (basePath) {
        if (basePath === void 0) { basePath = ""; }
        if (!this._classNames) {
            if (basePath && typeof basePath == 'string') {
                this._classNames = [];
                var scripts = document.getElementsByTagName('script');
                if (scripts && scripts.length) {
                    for (var i in scripts) {
                        if (scripts[i].src && scripts[i].src.indexOf(basePath) > -1) {
                            var pathArray = scripts[i].src.split("/");
                            var className = pathArray.pop().split(".")[0];
                            if (this._classNames.indexOf(className) == -1) {
                                this._classNames.push(className);
                            }
                        }
                    }
                }
            }
            else {
                this._nameSpace = basePath;
                this._classNames = Object.keys(basePath);
            }
            this._classNames.forEach(function (className) {
                //init DataStoreManager holders
                DataStoreManager._actualModel[className] = {};
                DataStoreManager._actualModel[className].Data = [];
                DataStoreManager._actualModel[className].ViewModelWatcher = [];
                // initting promises below shouldn't actually be necessary, but is here for completion
                DataStoreManager._actualModel[className].getAllPromise = new Promise(function () { });
                DataStoreManager._actualModel[className].getByIdPromise = new Promise(function () { });
            });
        }
        return this._classNames;
    };
    /**
     * Creates and returns class instance of given className, if possible.
     *
     * @param className
     */
    InstanceFactory.createInstance = function (className) {
        if (this._classNames && this._classNames.indexOf(className) > -1) {
            return new this._nameSpace[className]();
        }
        else if (this._nameSpace[className]) {
            console.log(className + " not in approved ClassNames, but exists. Trying to create...");
            return new this._nameSpace[className]();
        }
        else {
            //console.log("No such class as " + className);
        }
    };
    /**
     * Crawls through passed data and its children, creating class instances as needed.
     * TODO: Needs to be optimized. Can check if conversion has already been done at current depth and 'continue' to next, if so.
     * @param data
     */
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
                    // if either parentNode isn't a FluxCompositerBase, OR it is and parentNode[prop] is NOT its viewModelWatcher, OR parentNode[prop] is the viewModelWatcher, but isn't composed yet...
                    if (!(parentNode instanceof FluxCompositerBase && parentNode.viewModelWatcher == parentNode[prop] && parentNode.viewModelWatcher instanceof FluxCompositerBase)) {
                        if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                            var instance = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                            if (instance) {
                                instance = parentNode[prop] = InstanceFactory.copyProperties(instance, parentNode[prop]); // set instance
                                instance.onFulfill();
                            }
                        }
                        drillDown(parentNode[prop]);
                    }
                }
            }
        };
        drillDown(data);
        return data;
    };
    /**
     * Creates child instances based on passed CompositionMapping and adds them to the appropriate property of parent class.
     *
     * @param compMap
     * @param parent
     */
    InstanceFactory.getChildInstances = function (compMap, parent) {
        var _this = this;
        parent[compMap.PropertyName] = []; // clear property
        var childStore = DataStoreManager._actualModel[compMap.ChildType].Data;
        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
            childStore.forEach(function (value) {
                if (value[compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    parent[compMap.PropertyName].push(value.viewModelWatcher);
                }
            });
        }
        else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                // Get the gerunds
                if (DataStoreManager._actualModel[compMap.GerundName]) {
                    if (DataStoreManager._actualModel[compMap.GerundName].Data) {
                        var d = DataStoreManager._actualModel[compMap.GerundName].Data;
                        var gerundLen = d.length;
                        var _loop_1 = function (i) {
                            childStore.forEach(function (value) {
                                if (value.UID == d[i].ChildId && parent.UID == d[i].ParentId) {
                                    parent[compMap.PropertyName].push(value.viewModelWatcher);
                                }
                            });
                        };
                        //loop through all the gerunds
                        for (var i = 0; i < gerundLen; i++) {
                            _loop_1(i);
                        }
                    }
                    else {
                        DataStoreManager._actualModel[compMap.GerundName].promise = (DataStoreManager._actualModel[compMap.GerundName].promise || XHR.GET(compMap.GerundUrl))
                            .then(function (gerundReturns) {
                            if (gerundReturns) {
                                var d = DataStoreManager._actualModel[compMap.GerundName].Data = gerundReturns;
                                var gerundLen = d.length;
                                var _loop_2 = function (i) {
                                    childStore.forEach(function (value) {
                                        if (value.UID == d[i].ChildId && parent.UID == d[i].ParentId) {
                                            parent[compMap.PropertyName].push(value.viewModelWatcher);
                                        }
                                    });
                                };
                                //loop through all the gerunds
                                for (var i = 0; i < gerundLen; i++) {
                                    _loop_2(i);
                                }
                            }
                        });
                        console.log(compMap.GerundName + " doesn't exist in actualModel. Running GET to resolve...");
                    }
                }
                else {
                    DataStoreManager.getById(parent.TypeName, parent.UID, new ViewModelHolder(parent), [compMap]);
                    console.log(compMap.GerundName + " doesn't exist in actualModel. Running getById to resolve...");
                }
            }
            else {
                parent[compMap.PropertyName + "Promise"] = (parent[compMap.PropertyName + "Promise"] || XHR.GET(parent.getChildUrl(compMap)))
                    .then(function (d) {
                    d = InstanceFactory.convertToClasses(d);
                    var len = d.length;
                    for (var i = 0; i < len; i++) {
                        var current = d[i];
                        _this.commitToActualModel(current);
                        parent[compMap.PropertyName].push(current);
                    }
                    return d;
                });
            }
        }
        else {
            childStore.forEach(function (value) {
                if (value[compMap.ParentIdProp] == parent[compMap.ChildIdProp]) {
                    parent[compMap.PropertyName] = value.viewModelWatcher;
                }
            });
        }
    };
    /**
     * Copies properties/values from source to target and returns modified target.
     * It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy NOT working.
     *
     * @param target
     * @param source
     * @param exclusions
     */
    InstanceFactory.copyProperties = function (target, source, exclusions) {
        if (exclusions === void 0) { exclusions = []; }
        if (!target)
            target = {}; // init target, if doesn't already exist
        var sourceCopy = {};
        for (var prop in source) {
            if (exclusions.indexOf(prop) == -1) {
                // only copy over props that are not excluded
                sourceCopy[prop] = source[prop];
            }
        }
        Object.defineProperties(target, Object.getOwnPropertyNames(sourceCopy).reduce(function (descriptors, key) {
            descriptors[key] = Object.getOwnPropertyDescriptor(sourceCopy, key);
            return descriptors;
        }, {}));
        return target;
    };
    InstanceFactory._nameSpace = window;
    return InstanceFactory;
}(DataStoreManager));
