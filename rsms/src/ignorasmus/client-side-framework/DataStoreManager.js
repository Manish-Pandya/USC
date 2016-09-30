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
    PermissionMap.getPermission = function (className) {
        if (!_.has(this.Permissions, className)) {
            this.Permissions[className] = {};
            this.Permissions[className].getAll = new window[className]().hasGetAllPermission();
        }
        console.log(this.Permissions);
        return this.Permissions[className];
    };
    PermissionMap.Permissions = [];
    return PermissionMap;
}());
//abstract specifies singleton in ts 1.x (ish)
var DataStoreManager = (function () {
    function DataStoreManager() {
    }
    Object.defineProperty(DataStoreManager, "ActualModel", {
        get: function () {
            return this._actualModel;
        },
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
    DataStoreManager.getAll = function (type, viewModelParent, compMaps) {
        var _this = this;
        if (compMaps === void 0) { compMaps = null; }
        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].Data || !DataStoreManager._actualModel[type].Data.length) {
            //console.log(type + " before request");
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                return DataStoreManager._actualModel[type].getAllPromise = XHR.GET(window[type].urlMapping.urlGetAll)
                    .then(function (d) {
                    //console.log(type + " after request");
                    DataStoreManager._actualModel[type].Data = InstanceFactory.convertToClasses(d);
                    if (compMaps) {
                        var allComps = [];
                        var thisClass = DataStoreManager._actualModel[type].Data[0];
                        if (thisClass["Class"] == "PrincipalInvestigator" && thisClass["Key_id"] == 1) {
                            thisClass.onFulfill();
                            _this.fullfillProperty(thisClass, thisClass["RoomMap"]);
                        }
                        for (var instanceProp in thisClass) {
                            if (thisClass[instanceProp] instanceof CompositionMapping && thisClass[instanceProp].CompositionType != CompositionMapping.ONE_TO_ONE) {
                                if (PermissionMap.getPermission(thisClass[instanceProp].ChildType).getAll) {
                                    if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && compMaps.indexOf(thisClass[instanceProp]) > -1)) {
                                        if (!DataStoreManager._actualModel[thisClass[instanceProp].ChildType].Data) {
                                            console.log(type + " in if looking for " + thisClass[instanceProp].ChildType);
                                            //allComps.push(DataStoreManager.getAll(thisClass[instanceProp].ChildType, []));
                                            if (DataStoreManager._actualModel[thisClass[instanceProp].ChildType].getAllCalled) {
                                                allComps.push(DataStoreManager._actualModel[thisClass[instanceProp].ChildType].getAllPromise);
                                            }
                                            else {
                                                allComps.push(DataStoreManager.getAll(thisClass[instanceProp].ChildType, []));
                                            }
                                        }
                                        else {
                                            console.log(type + " in else looking for " + thisClass[instanceProp].ChildType);
                                            allComps.push(DataStoreManager._actualModel[thisClass[instanceProp].ChildType].Data);
                                        }
                                    }
                                }
                                else {
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
                        //DIG:  DataStoreManager._actualModel[type].Data is the holder for the actual data of this type.
                        //Time to decide for sure.  Do we have a seperate hashmap object, is Data a mapped object, or do we not need the performance boost of mapping at all?
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
            //console.log("hmm:", DataStoreManager._actualModel[type].Data);
            var d = DataStoreManager._actualModel[type].Data;
            d.forEach(function (value, index, array) {
                if (!value.viewModelWatcher) {
                    value.viewModelWatcher = _.cloneDeep(value);
                }
                viewModelParent[index] = value.viewModelWatcher;
                value.doCompose(compMaps);
            });
            console.log(type, DataStoreManager._actualModel[type].Data);
            //DataStoreManager._actualModel[type].Data = d;
            return this.promisifyData(DataStoreManager._actualModel[type].Data);
        }
    };
    DataStoreManager.getById = function (type, id, viewModelParent, compMaps) {
        var _this = this;
        if (compMaps === void 0) { compMaps = null; }
        id = id.toString();
        if (!this._actualModel[type].Data || !this._actualModel[type].Data.length) {
            return DataStoreManager._actualModel[type].getByIdPromise = XHR.GET(window[type].urlMapping.urlGetById + id)
                .then(function (d) {
                if (compMaps) {
                    var allComps = [];
                    var thisClass = window[type];
                    for (var instanceProp in thisClass) {
                        if (thisClass[instanceProp] instanceof CompositionMapping && thisClass[instanceProp].CompositionType != CompositionMapping.ONE_TO_ONE) {
                            if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && compMaps.indexOf(thisClass[instanceProp]) > -1)) {
                                allComps.push(DataStoreManager.getAll(thisClass[instanceProp].ChildType, []));
                            }
                        }
                    }
                    return Promise.all(allComps)
                        .then(function (whateverGotReturned) {
                        d = InstanceFactory.convertToClasses(d);
                        if (!d.viewModelWatcher) {
                            d.viewModelWatcher = _.cloneDeep(d);
                        }
                        viewModelParent = d.viewModelWatcher;
                        d.doCompose(compMaps);
                        var existingIndex = _.findIndex(DataStoreManager._actualModel[type].Data, function (o) { return o.UID == d.UID; });
                        if (existingIndex > -1) {
                            DataStoreManager._actualModel[type].Data[existingIndex] = d;
                        }
                        else {
                            DataStoreManager._actualModel[type].Data.push(d);
                        }
                        return viewModelParent;
                    })
                        .catch(function (reason) {
                        console.log("getById (inner promise):", reason);
                    });
                }
                else {
                    console.log(d);
                    //viewModelParent = _.assign(viewModelParent, d);
                    // console.log(viewModelParent);
                    //return viewModelParent;
                    var d = InstanceFactory.convertToClasses(d);
                    var existingIndex = _.findIndex(DataStoreManager._actualModel[type].Data, function (o) { return o.UID == d.UID; });
                    if (existingIndex > -1) {
                        DataStoreManager._actualModel[type].Data[existingIndex] = d;
                    }
                    else {
                        DataStoreManager._actualModel[type].Data.push(d);
                    }
                    if (!d.viewModelWatcher) {
                        d.viewModelWatcher = _.cloneDeep(d);
                    }
                    //TODO Figger thisun' out: do we have to _assign here?  I hope not, because we really need viewModelParent to be a reference to viewModelWatcher
                    viewModelParent = d.viewModelWatcher;
                    d.doCompose(compMaps);
                    //DataStoreManager._actualModel[type].Data = d;
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
        /*var obj: any = this.findByPropValue(this._actualModel[type], this.uidString, id);
        if (obj) {
            _.assign(viewModelParent, obj);
        } else {
            throw new Error("No such id as " + id + " already in actual model.");
        }*/
    };
    DataStoreManager.fullfillProperty = function (parent, compMap) {
        return parent[compMap.PropertyName + "Promise"] = XHR.GET(compMap.ChildUrl).then(function (d) {
            console.log(d);
            return d;
        });
    };
    // TODO: Doesn't always work, as drills into object nest before moving to next object.
    DataStoreManager.getActualModelEquivalent = function (viewModelObj) {
        if (Array.isArray(viewModelObj)) {
            console.log("hey man... i expected this to be a single instance of an approved class");
        }
        else {
            if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName]) > -1) {
                /*for (var n: number = 0; n < this._actualModel[viewModelObj[this.classPropName]].Data.length; n++) {
                    if (this._actualModel[viewModelObj[this.classPropName]].Data[n].Key_id == "3") {
                        console.log(n, this._actualModel[viewModelObj[this.classPropName]].Data[n]);
                    }
                }*/
                viewModelObj = this.findByPropValue(this._actualModel[viewModelObj[this.classPropName]].Data, this.uidString, viewModelObj[this.uidString]);
                return viewModelObj;
            }
            else {
                console.log("dang dude... I'm not familiar with this class or object type");
            }
        }
    };
    DataStoreManager.commitToActualModel = function (viewModelParent) {
        // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
        this._actualModel = _.cloneDeep(viewModelParent);
        return true;
    };
    // TODO: Return a USEFULL error if anything on ActualModel is passed for propParent
    DataStoreManager.setViewModelProp = function (propParent, propName, value, optionalCallBack) {
        propParent[propName] = value;
        if (optionalCallBack) {
            optionalCallBack();
        }
    };
    // also works for simply finding object by id: findByPropValue(obj, "id", "someId");
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
    DataStoreManager.promisifyData = function (data) {
        console.log("got here", data);
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
            console.log("this is ok", data.Class);
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
    // NOTE: there's intentionally no getter
    DataStoreManager._actualModel = {};
    return DataStoreManager;
}());
