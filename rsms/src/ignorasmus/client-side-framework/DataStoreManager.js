////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var PermissionMap = (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    function PermissionMap() {
    } // Static class cannot be instantiated
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    /**
     * Returns the permissions of the given class type.
     *
     * @param className
     */
    PermissionMap.getPermission = function (className) {
        if (!_.has(this.Permissions, className)) {
            this.Permissions[className] = {};
            var instance = InstanceFactory.createInstance(className);
            this.Permissions[className].getAll = instance.hasGetAllPermission();
        }
        return this.Permissions[className];
    };
    return PermissionMap;
}());
//----------------------------------------------------------------------
//
//  Properties
//
//----------------------------------------------------------------------
PermissionMap.Permissions = [];
//abstract specifies singleton in ts 1.x (ish)
var DataStoreManager = (function () {
    function DataStoreManager() {
    }
    Object.defineProperty(DataStoreManager, "ActualModel", {
        set: function (value) {
            this._actualModel = InstanceFactory.convertToClasses(value);
        },
        enumerable: true,
        configurable: true
    });
    Object.defineProperty(DataStoreManager, "ModalData", {
        get: function () {
            return this._modalData;
        },
        set: function (value) {
            this._modalData = _.cloneDeep(value);
        },
        enumerable: true,
        configurable: true
    });
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    /**
     * Gets all instances of a given type and passes them to the given viewModelParent.
     * Optionally composes child classes based on passed CompositionMapping.
     *
     * @param type
     * @param viewModelParent
     * @param compMaps
     */
    //TODO:  Switch of allCompMaps when we hit circular structure in get alls, for instance, a PI can get its Rooms which can get its PIs, but we should stop there.
    DataStoreManager.getAll = function (type, viewModelParent, compMaps) {
        var _this = this;
        if (compMaps === void 0) { compMaps = null; }
        if (!PermissionMap.getPermission(type).getAll) {
            throw new Error("You don't have permission to call getAll for " + type);
        }
        if (!InstanceFactory._classNames)
            InstanceFactory.getClassNames("/models");
        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].Data || !DataStoreManager._actualModel[type].Data.length) {
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                DataStoreManager._actualModel[type].getAllPromise = XHR.GET(InstanceFactory._nameSpace[type].urlMapping.urlGetAll);
            }
        }
        else {
            DataStoreManager._actualModel[type].getAllPromise = this.promisifyData(DataStoreManager._actualModel[type].Data);
        }
        return DataStoreManager._actualModel[type].getAllPromise
            .then(function (d) {
            if (d.length) {
                d = InstanceFactory.convertToClasses(d);
                DataStoreManager._actualModel[type].Data = d;
                return (compMaps ? _this.resolveCompMaps(d[0], compMaps) : _this.promisifyData(d))
                    .then(function (whateverGotReturned) {
                    d.forEach(function (value, index) {
                        d[index].doCompose(compMaps);
                        d[index].viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(value);
                        viewModelParent[index] = d[index].viewModelWatcher;
                    });
                    return viewModelParent;
                })
                    .catch(function (reason) {
                    console.log("getAll (inner promise):", reason);
                });
            }
        })
            .catch(function (d) {
            console.log("getAll:", d);
            return d;
        });
    };
    /**
     * Gets instance of a given type by id and passes it to the given viewModelParent.
     * Optionally composes child classes based on passed CompositionMapping.
     *
     * @param type
     * @param id
     * @param viewModelParent
     * @param compMaps
     */
    DataStoreManager.getById = function (type, id, viewModelParent, compMaps) {
        var _this = this;
        if (compMaps === void 0) { compMaps = null; }
        if (!InstanceFactory._classNames)
            InstanceFactory.getClassNames("/models");
        id = id.toString();
        if (!this._actualModel[type].Data || !this._actualModel[type].Data.length) {
            DataStoreManager._actualModel[type].getByIdPromise = XHR.GET(InstanceFactory._nameSpace[type].urlMapping.urlGetById + id);
        }
        else {
            DataStoreManager._actualModel[type].getByIdPromise = this.promisifyData(this.findByPropValue(DataStoreManager._actualModel[type].Data, DataStoreManager.uidString, id, type));
        }
        return DataStoreManager._actualModel[type].getByIdPromise
            .then(function (d) {
            d = InstanceFactory.convertToClasses(d);
            var actualModelInstance = DataStoreManager.getActualModelEquivalent(d);
            if (actualModelInstance) {
                actualModelInstance = d;
            }
            else {
                DataStoreManager._actualModel[type].Data.push(d);
            }
            return (compMaps ? _this.resolveCompMaps(d, compMaps) : _this.promisifyData(d))
                .then(function (whateverGotReturned) {
                d.doCompose(compMaps);
                d.viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(d);
                viewModelParent = _.assign(viewModelParent, d.viewModelWatcher);
                return viewModelParent;
            })
                .catch(function (reason) {
                console.log("getById (inner promise):", reason);
            });
        })
            .catch(function (d) {
            console.log("getById:", d);
            return d;
        });
    };
    DataStoreManager.resolveCompMaps = function (fluxCompositerBase, compMaps) {
        var _this = this;
        var allComps = [];
        if (compMaps) {
            fluxCompositerBase.allCompMaps.forEach(function (compMap) {
                // if compMaps == true or if it's an array with an approved compMap...
                if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && _.findIndex(compMaps, compMap) > -1)) {
                    if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                        if (!DataStoreManager._actualModel[compMap.ChildType].Data || !DataStoreManager._actualModel[compMap.ChildType].Data.length) {
                            console.log(fluxCompositerBase.TypeName + " fetching remote " + compMap.ChildType);
                            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled) {
                                allComps.push(DataStoreManager._actualModel[compMap.ChildType].getAllPromise);
                            }
                            else {
                                allComps.push(DataStoreManager.getAll(compMap.ChildType, [], typeof compMaps === "boolean"));
                            }
                        }
                        else {
                            console.log(fluxCompositerBase.TypeName + " fetching local " + compMap.ChildType);
                            allComps.push(DataStoreManager._actualModel[compMap.ChildType].Data);
                        }
                        if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
                            if (!DataStoreManager._actualModel[compMap.GerundName] || !DataStoreManager._actualModel[compMap.GerundName].promise) {
                                DataStoreManager._actualModel[compMap.GerundName] = {}; // clear property
                                console.log(fluxCompositerBase.TypeName + "'s", compMap.GerundName, "gerund getting baked...");
                                DataStoreManager._actualModel[compMap.GerundName].promise = XHR.GET(compMap.GerundUrl)
                                    .then(function (gerundReturns) {
                                    DataStoreManager._actualModel[compMap.GerundName].Data = gerundReturns;
                                });
                                allComps.push(DataStoreManager._actualModel[compMap.GerundName].promise);
                            }
                            else {
                                allComps.push(DataStoreManager._actualModel[compMap.GerundName].Data);
                            }
                        }
                    }
                    else {
                        console.log(compMap.ChildType + " has no getAll permission, so resolving childUrl...");
                        fluxCompositerBase[compMap.PropertyName + "Promise"] = (fluxCompositerBase[compMap.PropertyName + "Promise"] || XHR.GET(fluxCompositerBase.getChildUrl(compMap)))
                            .then(function (d) {
                            d = InstanceFactory.convertToClasses(d);
                            if (Array.isArray(d)) {
                                var len = d.length;
                                for (var i = 0; i < d.length; i++) {
                                    _this.commitToActualModel(d[i]);
                                }
                            }
                        });
                        allComps.push(fluxCompositerBase[compMap.PropertyName + "Promise"]);
                    }
                }
            });
        }
        return Promise.all(allComps);
    };
    /**
     * Saves the passed viewModel instance and sets the actualModel after success.
     *
     * @param viewModel
     */
    DataStoreManager.save = function (viewModel) {
        // if viewModel is array, add 's' to end of save url to differentiate it as plural call on the server
        var urlSave = Array.isArray(viewModel) ? viewModel[0].thisClass["urlMapping"].urlSave + "s" : viewModel.thisClass["urlMapping"].urlSave;
        return XHR.POST(urlSave, viewModel)
            .then(function (d) {
            if (Array.isArray(d)) {
                d.forEach(function (value, index) {
                    d[index] = DataStoreManager.commitToActualModel(d[index]);
                });
                return d;
            }
            return DataStoreManager.commitToActualModel(d);
        });
    };
    /**
     * Returns the actualModel instance equivalent of a given viewModel, if found.
     *
     * @param viewModelObj
     */
    DataStoreManager.getActualModelEquivalent = function (viewModelObj) {
        if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName]) > -1) {
            var existingIndex = _.findIndex(DataStoreManager._actualModel[viewModelObj[this.classPropName]].Data, function (o) { return o.UID == viewModelObj.UID; });
            if (existingIndex > -1) {
                return DataStoreManager._actualModel[viewModelObj[this.classPropName]].Data[existingIndex];
            }
        }
        else {
            console.log("dang dude... I'm not familiar with this class or object type");
        }
    };
    // TODO... consider allowing array of instances rather than just 1 instance.
    /**
     * Copies the properties of viewModelParent to equivalent instance in actualModel, if found.
     * Otherwise, pushes viewModelParent to actualModel, if not already there.
     *
     * @param viewModelParent
     */
    DataStoreManager.commitToActualModel = function (viewModelParent) {
        var vmParent = InstanceFactory.convertToClasses(viewModelParent);
        if (!(vmParent instanceof FluxCompositerBase))
            return;
        var actualModelEquivalent = this.getActualModelEquivalent(vmParent);
        if (!actualModelEquivalent && vmParent.TypeName) {
            DataStoreManager._actualModel[vmParent.TypeName].Data.push(_.cloneDeep(vmParent));
            actualModelEquivalent = this.getActualModelEquivalent(vmParent);
        }
        vmParent = InstanceFactory.copyProperties(actualModelEquivalent, vmParent);
        actualModelEquivalent.viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(actualModelEquivalent);
        return vmParent.viewModelWatcher;
    };
    /**
     * Returns fluxBase quazi-clone and recursively sets all sub-models to reference their respective viewModelWatchers, as opposed to independent copies or references to ActualModel.
     * So... changes on any deep-nested FluxCompositerBase will reflect Everywhere that FluxCompositerBase's viewModelWatcher is referenced.
     * Full roundtrip, depth-independent model syncing!
     *
     * @param fluxBase
     */
    DataStoreManager.buildNestedViewModelWatcher = function (fluxBase) {
        if (fluxBase.hasOwnProperty("viewModelWatcher")) {
            fluxBase.viewModelWatcher = InstanceFactory.convertToClasses(InstanceFactory.copyProperties(fluxBase.viewModelWatcher, fluxBase));
        }
        return _.cloneDeepWith(fluxBase, function (value) {
            if (value instanceof FluxCompositerBase) {
                if (value.viewModelWatcher) {
                    // set reference to nested FluxCompositerBase's viewModelWatcher, instead of cloning
                    delete value.viewModelWatcher.viewModelWatcher;
                    return value.viewModelWatcher;
                }
            }
        });
    };
    /**
     * Resets a given viewModel instance with the actualModel equivalent instance's properties.
     *
     * @param viewModelParent
     */
    DataStoreManager.undo = function (viewModelParent) {
        var actualModelInstance = this.getActualModelEquivalent(viewModelParent);
        if (actualModelInstance) {
            InstanceFactory.copyProperties(actualModelInstance.viewModelWatcher, actualModelInstance, ["viewModelWatcher"]);
        }
    };
    /**
     * Returns an object in a given complex object or collection by a property/value pair.
     * Also works for simply finding object by id: findByPropValue(obj, "id", "someId");
     *
     * @param obj
     * @param propName
     * @param value
     */
    DataStoreManager.findByPropValue = function (obj, propName, value, className) {
        //Early return
        if ((!className || className == obj.constructor.name) && obj[propName] === value) {
            return obj;
        }
        var result;
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop) && obj[prop] && typeof obj[prop] === 'object') {
                result = this.findByPropValue(obj[prop], propName, value, className);
                if (result) {
                    return result;
                }
            }
        }
    };
    /**
     * Returns a Promise for data passed.
     * Also works fine if data passed is already a Promise.
     *
     * @param data
     */
    DataStoreManager.promisifyData = function (data) {
        var p = new Promise(function (resolve, reject) {
            if (data) {
                resolve(data);
            }
            else {
                reject("bad in dsm");
            }
        });
        return p;
    };
    return DataStoreManager;
}());
//----------------------------------------------------------------------
//
//  Properties
//
//----------------------------------------------------------------------
DataStoreManager.classPropName = "Class";
DataStoreManager.uidString = "Key_id";
DataStoreManager.baseUrl = "../ajaxaction.php?action=";
// NOTE: there's intentionally no getter. Only internal framework classes should have read access of actual model.
DataStoreManager._actualModel = {};
