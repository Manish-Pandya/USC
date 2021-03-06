////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var PermissionMap = /** @class */ (function () {
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
            //TODO:  this.Permissions[className].save = instance.getHasSavePermissions();
        }
        return this.Permissions[className];
    };
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------
    PermissionMap.Permissions = [];
    return PermissionMap;
}());
//abstract specifies singleton in ts 1.x (ish)
var DataStoreManager = /** @class */ (function () {
    function DataStoreManager() {
    }
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
    DataStoreManager.getAll = function (type, viewModelInst, compMaps) {
        var _this = this;
        if (compMaps === void 0) { compMaps = null; }
        if (!PermissionMap.getPermission(type).getAll) {
            throw new Error("You don't have permission to call getAll for " + type);
        }
        if (!InstanceFactory._classNames)
            InstanceFactory.getClassNames("/models");
        if (!viewModelInst.data)
            viewModelInst.data = [];
        viewModelInst.data.splice(0, viewModelInst.data.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].getAllCalled) {
            DataStoreManager._actualModel[type].getAllCalled = true;
            DataStoreManager._actualModel[type].getAllPromise = XHR.GET(InstanceFactory._nameSpace[type].urlMapping.urlGetAll);
        }
        else if (DataStoreManager._actualModel[type].Data && DataStoreManager._actualModel[type].Data.length) {
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
                        d[index].viewModelWatcher = DataStoreManager._actualModel[type].ViewModelWatcher[index] = DataStoreManager.buildNestedViewModelWatcher(value);
                    });
                    if (compMaps && typeof compMaps === "boolean") {
                        DataStoreManager._actualModel[type].fullyComposed = true;
                    }
                    viewModelInst.data = DataStoreManager._actualModel[type].ViewModelWatcher;
                    return viewModelInst;
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
    DataStoreManager.getById = function (type, id, viewModelInst, compMaps) {
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
            var existingIndex = _.findIndex(DataStoreManager._actualModel[type].Data, function (o) { return o.UID == d.UID; });
            if (existingIndex > -1) {
                DataStoreManager._actualModel[type].Data[existingIndex] = d; // update existing
            }
            else {
                existingIndex = DataStoreManager._actualModel[type].Data.push(d) - 1; // add new
            }
            return (compMaps ? _this.resolveCompMaps(d, compMaps) : _this.promisifyData(d))
                .then(function (whateverGotReturned) {
                d.doCompose(compMaps);
                d.viewModelWatcher = viewModelInst.data = DataStoreManager._actualModel[type].ViewModelWatcher[existingIndex] = DataStoreManager.buildNestedViewModelWatcher(d);
                if (compMaps && typeof compMaps === "boolean") {
                    DataStoreManager._actualModel[type].fullyComposed = true;
                }
                return viewModelInst;
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
    DataStoreManager.resolveCompMaps = function (fluxCompBase, compMaps) {
        var _this = this;
        var allComps = [];
        if (compMaps) {
            fluxCompBase.allCompMaps.forEach(function (compMap) {
                // if compMaps == true or if it's an array with an approved compMap...
                if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && _.findIndex(compMaps, compMap) > -1)) {
                    if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                        var needsNestedComposing = typeof compMaps === "boolean" && !DataStoreManager._actualModel[compMap.ChildType].fullyComposed;
                        if (!DataStoreManager._actualModel[compMap.ChildType].Data || !DataStoreManager._actualModel[compMap.ChildType].Data.length || needsNestedComposing) {
                            console.log(fluxCompBase.TypeName + " fetching remote " + compMap.ChildType);
                            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled && !needsNestedComposing) {
                                allComps.push(DataStoreManager._actualModel[compMap.ChildType].getAllPromise);
                            }
                            else {
                                allComps.push(DataStoreManager.getAll(compMap.ChildType, new ViewModelHolder(), typeof compMaps === "boolean"));
                            }
                        }
                        else {
                            console.log(fluxCompBase.TypeName + " fetching local " + compMap.ChildType);
                            allComps.push(DataStoreManager._actualModel[compMap.ChildType].Data);
                        }
                        if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
                            if (!DataStoreManager._actualModel[compMap.GerundName] || !DataStoreManager._actualModel[compMap.GerundName].promise) {
                                DataStoreManager._actualModel[compMap.GerundName] = {}; // clear property
                                console.log(fluxCompBase.TypeName + "'s", compMap.GerundName, "gerund getting baked...");
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
                        fluxCompBase[compMap.PropertyName + "Promise"] = (fluxCompBase[compMap.PropertyName + "Promise"] || XHR.GET(fluxCompBase.getChildUrl(compMap)))
                            .then(function (d) {
                            d = InstanceFactory.convertToClasses(d);
                            if (Array.isArray(d)) {
                                var len = d.length;
                                for (var i = 0; i < d.length; i++) {
                                    _this.commitToActualModel(d[i]);
                                }
                            }
                        });
                        allComps.push(fluxCompBase[compMap.PropertyName + "Promise"]);
                        //throw new Error("You don't have permission to call getAll for " + compMap.ChildType);
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
    DataStoreManager.save = function (viewModel, reverseCompose) {
        if (reverseCompose === void 0) { reverseCompose = true; }
        viewModel = InstanceFactory.convertToClasses(viewModel); // Ensure viewModel is FluxCompositerBase(s).
        // if viewModel is array, add 's' to end of save url to differentiate it as plural call on the server
        var urlSave = Array.isArray(viewModel) ? viewModel[0].thisClass["urlMapping"].urlSave + "s" : viewModel.thisClass["urlMapping"].urlSave;
        return XHR.POST(urlSave, viewModel)
            .then(function (d) {
            if (Array.isArray(d)) {
                d.forEach(function (value, index) {
                    d[index] = DataStoreManager.commitToActualModel(d[index], reverseCompose);
                });
                return d;
            }
            return DataStoreManager.commitToActualModel(d, reverseCompose);
        });
    };
    /**
     * Returns the actualModel instance equivalent of a given viewModel, if found.
     *
     * @param viewModelObj
     */
    DataStoreManager.getActualModelEquivalent = function (fluxCompBase) {
        if (fluxCompBase[this.classPropName] && InstanceFactory._classNames.indexOf(fluxCompBase[this.classPropName]) > -1) {
            var existingIndex = _.findIndex(DataStoreManager._actualModel[fluxCompBase[this.classPropName]].Data, function (o) { return o.UID == fluxCompBase.UID; });
            if (existingIndex > -1) {
                return DataStoreManager._actualModel[fluxCompBase[this.classPropName]].Data[existingIndex];
            }
        }
        else {
            console.log("dang... I'm not familiar with this class or object type");
        }
    };
    // TODO... consider allowing array of instances rather than just 1 instance.
    /**
     * Copies the properties of viewModelParent to equivalent instance in actualModel, if found.
     * Otherwise, pushes viewModelParent to actualModel, if not already there.
     *
     * @param viewModelParent
     */
    DataStoreManager.commitToActualModel = function (viewModelParent, reverseCompose) {
        if (reverseCompose === void 0) { reverseCompose = true; }
        var vmParent = InstanceFactory.convertToClasses(viewModelParent);
        if (!(vmParent instanceof FluxCompositerBase))
            return;
        var actualModelEquivalent = this.getActualModelEquivalent(vmParent);
        if (!actualModelEquivalent && vmParent.TypeName) {
            var isNew = true;
            DataStoreManager._actualModel[vmParent.TypeName].Data.push(_.cloneDeep(vmParent));
            actualModelEquivalent = this.getActualModelEquivalent(vmParent);
        }
        vmParent = InstanceFactory.copyProperties(actualModelEquivalent, vmParent, ["viewModelWatcher"]);
        actualModelEquivalent.viewModelWatcher = vmParent.viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(actualModelEquivalent);
        if (isNew) {
            DataStoreManager._actualModel[vmParent.TypeName].ViewModelWatcher.push(vmParent.viewModelWatcher);
            if (reverseCompose) {
                // See if any instances in actualModel should compose collections of vmParent's class-types and add it to collection //
                InstanceFactory._classNames.forEach(function (className) {
                    var fluxClass = InstanceFactory._nameSpace[className];
                    for (var instanceProp in fluxClass) {
                        if (fluxClass[instanceProp] instanceof CompositionMapping) {
                            var cm = fluxClass[instanceProp];
                            if (InstanceFactory._classNames.indexOf(cm.ChildType) > -1) {
                                // if the CompMap's ChildType is same class-type as vmParent...
                                if (cm.ChildType == vmParent.TypeName && vmParent[cm.ChildIdProp]) {
                                    var existingIndex = _.findIndex(DataStoreManager._actualModel[fluxClass.name].Data, function (o) { return o[cm.ParentIdProp] == vmParent[cm.ChildIdProp]; });
                                    if (existingIndex > -1) {
                                        // We found actualModel instance that should compose this vmParent instance!
                                        var compParent = DataStoreManager._actualModel[fluxClass.name].Data[existingIndex];
                                        if (cm.CompositionType == CompositionMapping.ONE_TO_ONE) {
                                            compParent[cm.PropertyName] = vmParent.viewModelWatcher;
                                        }
                                        else {
                                            if (!compParent[cm.PropertyName])
                                                compParent[cm.PropertyName] = [];
                                            compParent[cm.PropertyName].push(vmParent.viewModelWatcher);
                                        }
                                    }
                                    // ...else if the compMap belongs to the vmParent and its property is not null...
                                }
                                else if (fluxClass.name == vmParent.TypeName && vmParent[cm.PropertyName]) {
                                    var fluxProps = InstanceFactory.convertToClasses(vmParent[cm.PropertyName]);
                                    fluxProps = Array.isArray(fluxProps) ? fluxProps : [fluxProps];
                                    // go set the actualModel's data to include fluxCompositer instances from the vmParent's property (vmParent[cm.PropertyName])
                                    DataStoreManager._actualModel[cm.ChildType].Data = _.unionBy(DataStoreManager._actualModel[cm.ChildType].Data, fluxProps, 'UID');
                                }
                            }
                        }
                    }
                });
            }
        }
        // loop thru all vmParent's compMaps...
        vmParent.allCompMaps.forEach(function (compMap) {
            // if compMap has GerundName AND actualModel has a collection of the same name...
            if (compMap.GerundName && DataStoreManager._actualModel[compMap.GerundName] && DataStoreManager._actualModel[compMap.GerundName].Data) {
                // delete that local gerund collection, as we need any subsequent many-to-many calls to come straight from the database, and not local client cache.
                delete DataStoreManager._actualModel[compMap.GerundName];
            }
        });
        return vmParent.viewModelWatcher;
    };
    /**
     * Returns fluxBase quazi-clone and recursively sets all sub-models to reference their respective viewModelWatchers, as opposed to independent copies or references to ActualModel.
     * So... changes on any deep-nested FluxCompositerBase will reflect Everywhere that FluxCompositerBase's viewModelWatcher is referenced.
     * Full roundtrip, depth-independent model syncing!
     *
     * @param fluxBase
     */
    DataStoreManager.buildNestedViewModelWatcher = function (fluxCompBase) {
        if (fluxCompBase.hasOwnProperty("viewModelWatcher")) {
            if (!fluxCompBase.viewModelWatcher)
                fluxCompBase.viewModelWatcher = {}; // make viewModelWatcher if null
            InstanceFactory.convertToClasses(InstanceFactory.copyProperties(fluxCompBase.viewModelWatcher, fluxCompBase, ["viewModelWatcher"]));
        }
        return _.cloneDeepWith(fluxCompBase, function (value) {
            if (value instanceof FluxCompositerBase) {
                if (value.viewModelWatcher) {
                    // set reference to nested FluxCompositerBase's viewModelWatcher, instead of cloning
                    delete value.viewModelWatcher.viewModelWatcher;
                    return value.viewModelWatcher;
                }
                // otherwise, default deep-cloning happens
            }
        });
    };
    /**
     * Resets a given viewModel instance with the actualModel equivalent instance's properties.
     *
     * @param viewModelParent
     */
    DataStoreManager.undo = function (fluxCompBase) {
        var actualModelInstance = this.getActualModelEquivalent(fluxCompBase);
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
     * @param className
     */
    DataStoreManager.findByPropValue = function (obj, propName, value, className) {
        if ((!className || className == obj.constructor.name) && obj[propName] === value) {
            return obj; //Early return
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
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------
    DataStoreManager.classPropName = "Class";
    DataStoreManager.uidString = "Key_id";
    DataStoreManager.baseUrl = "../ajaxaction.php?action=";
    DataStoreManager._actualModel = {};
    return DataStoreManager;
}());
