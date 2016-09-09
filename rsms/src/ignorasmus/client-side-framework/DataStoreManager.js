////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
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
    // TODO: Consider method overload to allow multiple types and viewModelParents
    DataStoreManager.getAll = function (type, viewModelParent, compMaps) {
        if (compMaps === void 0) { compMaps = null; }
        if (!DataStoreManager._actualModel[type].Data) {
            //DataStoreManager._actualModel[type] = {};
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                return DataStoreManager._actualModel[type].getAllPromise = XHR.GET(window[type].urlMapping.urlGetAll)
                    .then(function (d) {
                    if (compMaps) {
                        var allComps = [];
                        var thisClass = window[type];
                        for (var instanceProp in thisClass) {
                            console.log(instanceProp);
                            if (thisClass[instanceProp] instanceof CompositionMapping && thisClass[instanceProp].CompositionType != CompositionMapping.ONE_TO_ONE) {
                                allComps.push(DataStoreManager.getAll(thisClass[instanceProp].ChildType, []));
                            }
                        }
                        return Promise.all(allComps)
                            .then(function (whateverGotReturned) {
                            viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
                            d = InstanceFactory.convertToClasses(d);
                            d.forEach(function (value, index, array) {
                                if (!value.viewModelWatcher) {
                                    value.viewModelWatcher = _.cloneDeep(value);
                                }
                                viewModelParent[index] = value.viewModelWatcher;
                                if (compMaps) {
                                    value.doCompose(compMaps);
                                }
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
                        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
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
            viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
            Array.prototype.push.apply(viewModelParent, _.cloneDeep(DataStoreManager._actualModel[type]));
            viewModelParent = _.cloneDeep(DataStoreManager._actualModel[type]);
        }
        return this.promisifyData(DataStoreManager._actualModel[type].getAllPromise);
    };
    DataStoreManager.getById = function (type, id, viewModelParent) {
        var obj = this.findByPropValue(this._actualModel[type], this.uidString, id);
        if (obj) {
            _.assign(viewModelParent, obj);
        }
        else {
            throw new Error("No such id as " + id + " already in actual model.");
        }
    };
    DataStoreManager.getActualModelEquivalent = function (viewModelObj) {
        if (Array.isArray(viewModelObj)) {
            console.log("hey man... i expected this to be a single instance of an approved class");
        }
        else {
            if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName])) {
                viewModelObj = this.findByPropValue(this.ActualModel[viewModelObj[this.classPropName]], this.uidString, viewModelObj[this.uidString]);
                return viewModelObj;
            }
            else {
                console.log("shit dude... I'm not familiar with this class or object type");
            }
        }
    };
    DataStoreManager.commitToActualModel = function (viewModelParent) {
        var success;
        if (success) {
            // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
            this._actualModel = _.cloneDeep(viewModelParent);
        }
        else {
            console.log("wtf");
        }
        return success;
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
    DataStoreManager.imBusy = false;
    // NOTE: there's intentionally no getter
    DataStoreManager._actualModel = {};
    return DataStoreManager;
}());
