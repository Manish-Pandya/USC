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
            var _this = this;
            var drillDown = function (parentNode) {
                for (var prop in parentNode) {
                    if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                        if (parentNode[prop].hasOwnProperty(_this.classPropName)) {
                            var instance = InstanceFactory.createInstance(parentNode[prop][_this.classPropName]);
                            parentNode[prop] = InstanceFactory.copyProperties(instance, parentNode[prop]);
                        }
                        drillDown(parentNode[prop]);
                    }
                }
            };
            drillDown(value);
            DataStoreManager._actualModel = value;
        },
        enumerable: true,
        configurable: true
    });
    DataStoreManager.storeThings = function (things) {
        if (!DataStoreManager.ActualModel[things[0].Class])
            DataStoreManager.ActualModel[things[0].Class] = {};
        for (var i = 0; i < things.length; i++) {
            DataStoreManager.ActualModel[things[0].Class][things[i].Key_id].Model = things[i];
        }
        if (this.isPromisified) {
        }
    };
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    DataStoreManager.getAll = function (type, viewModelParent) {
        if (viewModelParent === void 0) { viewModelParent = null; }
        viewModelParent = viewModelParent || this._actualModel;
        switch (type) {
            case "realSpecific":
                // junk stuff here
                break;
            default:
                if (!viewModelParent[type]) {
                    $.getJSON(window[type].urlAll)
                        .done(function (d) {
                        viewModelParent[type] = d;
                    })
                        .fail(function (d) {
                        console.log("shit... getJSON failed:", d.statusText);
                    })
                        .then(function (d) {
                        return DataStoreManager.promisifyData(viewModelParent[type]);
                    });
                }
                else {
                    return this.promisifyData(viewModelParent[type]);
                }
        }
    };
    DataStoreManager.getById = function (type, id, viewModelName) {
        var obj = this.findByPropValue(this._actualModel[type], this.uidString, id);
        if (obj && obj.viewModels && obj.viewModels.hasOwnProperty(viewModelName)) {
            return obj.viewModels[viewModelName];
        }
        else {
            throw new Error("No such id as " + id);
        }
    };
    DataStoreManager.syncViewModel = function (actualModelParent, viewModelName) {
        var _this = this;
        //set appropriate CurrentViewModel
        var viewParentNode = this.viewModels[viewModelName] = _.cloneDeep(actualModelParent);
        // get all classes from script tags
        var classNames = InstanceFactory.getClassNames("/rad/scripts/models/");
        // loop thru to set references
        var drillDown = function (parentNode, viewParentNode) {
            var className = parentNode.constructor.name;
            if (classNames.indexOf(className) > -1) {
                if (!parentNode.viewModels)
                    parentNode.viewModels = {};
                parentNode.viewModels[viewModelName] = viewParentNode; // Put actual reference by finding where it lives in viewModel
                if (!_this._actualModel[className]) {
                    _this._actualModel[className] = [];
                }
                _this._actualModel[className].push(parentNode);
            }
            for (var prop in parentNode) {
                if (parentNode.hasOwnProperty(prop) && prop != "viewModels" && parentNode[prop] && typeof parentNode[prop] === 'object') {
                    drillDown(parentNode[prop], viewParentNode[prop]);
                }
            }
        };
        drillDown(actualModelParent, viewParentNode);
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
            console.log(p);
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
    DataStoreManager.isPromisified = true;
    DataStoreManager.viewModels = {};
    // NOTE: there's intentionally no getter
    DataStoreManager._actualModel = {};
    return DataStoreManager;
}());
