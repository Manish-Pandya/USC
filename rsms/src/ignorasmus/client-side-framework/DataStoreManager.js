////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var PermissionMap = (function () {
    function PermissionMap() {
    }
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
            this.Permissions[className].getAll = new window[className]().hasGetAllPermission();
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
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    // TODO: Consider method overload to allow multiple types and viewModelParents
    /**
     * Gets all instances of a given type and passes them to the given viewModelParent.
     * Optionally composes child classes based on passed CompositionMapping.
     *
     * @param type
     * @param viewModelParent
     * @param compMaps
     */
    DataStoreManager.getAll = function (type, viewModelParent, compMaps) {
        if (compMaps === void 0) { compMaps = null; }
        if (!PermissionMap.getPermission(type).getAll) {
            throw new Error("You don't have permission to call getAll for " + type);
        }
        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].Data || !DataStoreManager._actualModel[type].Data.length) {
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                return DataStoreManager._actualModel[type].getAllPromise = XHR.GET(window[type].urlMapping.urlGetAll)
                    .then(function (d) {
                    DataStoreManager._actualModel[type].Data = InstanceFactory.convertToClasses(d);
                    if (compMaps) {
                        var allComps = [];
                        var allCompMaps = DataStoreManager._actualModel[type].Data[0].allCompMaps;
                        var l = allCompMaps.length;
                        for (var n = 0; n < l; n++) {
                            var compMap = allCompMaps[n];
                            if (compMap.CompositionType != CompositionMapping.ONE_TO_ONE) {
                                if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                                    // if compMaps == true or if it's an array with an approved compMap...
                                    if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && compMaps.indexOf(compMap) > -1)) {
                                        if (!DataStoreManager._actualModel[compMap.ChildType].Data || !DataStoreManager._actualModel[compMap.ChildType].Data.length) {
                                            console.log(type + " in if looking for " + compMap.ChildType);
                                            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled) {
                                                allComps.push(DataStoreManager._actualModel[compMap.ChildType].getAllPromise);
                                            }
                                            else {
                                                allComps.push(DataStoreManager.getAll(compMap.ChildType, []));
                                            }
                                        }
                                        else {
                                            console.log(type + " in else looking for " + compMap.ChildType);
                                            allComps.push(DataStoreManager._actualModel[compMap.ChildType].Data);
                                        }
                                    }
                                }
                            }
                        }
                        return Promise.all(allComps)
                            .then(function (whateverGotReturned) {
                            DataStoreManager._actualModel[type].Data.forEach(function (value, index, array) {
                                if (!value.viewModelWatcher) {
                                    value.viewModelWatcher = _.cloneDeep(value);
                                }
                                viewModelParent[index] = value.viewModelWatcher;
                                value.doCompose(compMaps);
                            });
                            DataStoreManager._actualModel[type].Data = d;
                            return viewModelParent;
                        })
                            .catch(function (reason) {
                            console.log("getAll (inner promise):", reason);
                        });
                    }
                    else {
                        d = InstanceFactory.convertToClasses(d);
                        //DIG: DataStoreManager._actualModel[type].Data is the holder for the actual data of this type.
                        DataStoreManager._actualModel[type].Data = d;
                        // Dig this neat way to use viewModelParent as a reference instead of a value!
                        Array.prototype.push.apply(viewModelParent, _.cloneDeep(d));
                        return viewModelParent;
                    }
                })
                    .catch(function (d) {
                    console.log("getAll:", d);
                    return d;
                });
            }
        }
        else {
            var d = DataStoreManager._actualModel[type].Data;
            d.forEach(function (value, index, array) {
                if (!value.viewModelWatcher) {
                    value.viewModelWatcher = _.cloneDeep(value);
                }
                viewModelParent[index] = value.viewModelWatcher;
                value.doCompose(compMaps);
            });
            return this.promisifyData(DataStoreManager._actualModel[type].Data);
        }
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
        id = id.toString();
        if (!this._actualModel[type].Data || !this._actualModel[type].Data.length) {
            return DataStoreManager._actualModel[type].getByIdPromise = XHR.GET(window[type].urlMapping.urlGetById + id)
                .then(function (d) {
                d = InstanceFactory.convertToClasses(d);
                _this.commitToActualModel(d);
                if (compMaps) {
                    var allComps = [];
                    var allCompMaps = DataStoreManager._actualModel[type].Data[0].allCompMaps;
                    var l = allCompMaps.length;
                    for (var n = 0; n < l; n++) {
                        var compMap = allCompMaps[n];
                        if (compMap.CompositionType != CompositionMapping.ONE_TO_ONE && DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                            // if compMaps == true or if it's an array with an approved compMap...
                            if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && compMaps.indexOf(compMap) > -1)) {
                                allComps.push(DataStoreManager.getAll(compMap.ChildType, []));
                            }
                        }
                    }
                    return Promise.all(allComps)
                        .then(function (whateverGotReturned) {
                        if (!d.viewModelWatcher) {
                            d.viewModelWatcher = _.cloneDeep(d);
                        }
                        d.doCompose(compMaps);
                        viewModelParent = _.assign(viewModelParent, d.viewModelWatcher);
                        return InstanceFactory.convertToClasses(viewModelParent);
                    })
                        .catch(function (reason) {
                        console.log("getById (inner promise):", reason);
                    });
                }
                else {
                    if (!d.viewModelWatcher) {
                        d.viewModelWatcher = _.cloneDeep(d);
                    }
                    //TODO Figger thisun' out: do we have to _assign here?  I hope not, because we really need viewModelParent to be a reference to viewModelWatcher
                    viewModelParent.test = d.viewModelWatcher;
                    viewModelParent = _.assign(viewModelParent, d.viewModelWatcher);
                    //DataStoreManager._actualModel[type].Data = d;
                    console.log("yup");
                    return _this.promisifyData(d);
                }
            })
                .catch(function (d) {
                console.log("getById:", d);
                return d;
            });
        }
        else {
            var d = this.findByPropValue(this._actualModel[type].Data, this.uidString, id);
            return InstanceFactory.convertToClasses(_.assign(viewModelParent, d));
        }
    };
    /**
     * Saves the passed viewModel instance and sets the actualModel after success.
     *
     * @param viewModel
     */
    DataStoreManager.save = function (viewModel) {
        //TODO: create copy without circular JSON, then post it.
        return XHR.POST(viewModel.thisClass["urlMapping"].urlSave, viewModel)
            .then(function (d) {
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
        var actualModelInstance = this.getActualModelEquivalent(vmParent);
        if (actualModelInstance) {
            vmParent = InstanceFactory.copyProperties(actualModelInstance, vmParent);
            InstanceFactory.copyProperties(actualModelInstance.viewModelWatcher, vmParent);
        }
        else {
            DataStoreManager._actualModel[vmParent.TypeName].Data.push(_.cloneDeep(vmParent));
            actualModelInstance = this.getActualModelEquivalent(vmParent);
            vmParent = InstanceFactory.copyProperties(actualModelInstance, vmParent);
            vmParent.viewModelWatcher = _.cloneDeep(vmParent);
        }
        //update vm watcher
        console.log(vmParent);
        return vmParent.viewModelWatcher;
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
    DataStoreManager.findByPropValue = function (obj, propName, value) {
        //Early return
        if (obj[propName] === value) {
            return obj;
        }
        var result;
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop) && obj[prop] && typeof obj[prop] === 'object') {
                result = this.findByPropValue(obj[prop], propName, value);
                if (result) {
                    return result;
                }
            }
        }
        return result;
    };
    /**
     * Returns a Promise for data passed.
     * Also works fine if data passed is already a Promise.
     *
     * @param data
     */
    DataStoreManager.promisifyData = function (data) {
        if (!this.isPromisified) {
            return data;
        }
        else {
            var p = new Promise(function (resolve, reject) {
                if (data) {
                    resolve(data);
                }
                else {
                    reject("bad in dsm");
                }
            });
            return p;
        }
    };
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------
    DataStoreManager.classPropName = "Class";
    DataStoreManager.uidString = "Key_id";
    DataStoreManager.baseUrl = "http://erasmus.graysail.com/rsms/src/ajaxAction.php?action=";
    DataStoreManager.isPromisified = true;
    // NOTE: there's intentionally no getter. Only internal framework classes should have read access of actual model.
    DataStoreManager._actualModel = {};
    return DataStoreManager;
}());
