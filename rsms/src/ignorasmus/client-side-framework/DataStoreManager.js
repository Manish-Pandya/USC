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
    Object.defineProperty(DataStoreManager, "CurrentViewModel", {
        get: function () {
            return DataStoreManager._currentViewModel;
        },
        set: function (value) {
            // TODO: compose clientside classes as needed via InstanceFactory. Compisition ONLY happend on viewModals.
            DataStoreManager._currentViewModel = value;
        },
        enumerable: true,
        configurable: true
    });
    Object.defineProperty(DataStoreManager, "CurrentModalViewModel", {
        get: function () {
            return DataStoreManager._currentModalViewModel;
        },
        set: function (value) {
            // TODO: compose clientside classes as needed via InstanceFactory. Compisition ONLY happend on viewModals.
            DataStoreManager._currentModalViewModel = value;
        },
        enumerable: true,
        configurable: true
    });
    Object.defineProperty(DataStoreManager, "ActualModel", {
        get: function () {
            return DataStoreManager._actualModel;
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
            DataStoreManager._currentViewModel = _.cloneDeep(value);
        },
        enumerable: true,
        configurable: true
    });
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    DataStoreManager.getAll = function (type, viewModelParent) {
        if (viewModelParent === void 0) { viewModelParent = this.CurrentViewModel; }
        switch (type) {
            case "realSpecific":
                // junk stuff here
                break;
            default:
                viewModelParent[type] = viewModelParent[type] || this.getAll(type, this.ActualModel);
                return viewModelParent[type];
        }
    };
    DataStoreManager.syncViewModel = function (actualModelParent, viewModelName) {
        if (viewModelName === void 0) { viewModelName = "CurrentViewModel"; }
        //set appropriate CurrentViewModel
        var viewParentNode = this[viewModelName] = _.cloneDeep(actualModelParent);
        // get all classes from script tags
        var classNames = InstanceFactory.getClassNames("/rad/scripts/models/");
        // loop thru to set references
        var drillDown = function (parentNode, viewParentNode) {
            var className = parentNode.constructor.name;
            if (classNames.indexOf(className) > -1) {
                parentNode.viewModelReference = viewParentNode; // Put actual reference by finding where it lives in viewModel
            }
            for (var prop in parentNode) {
                if (parentNode.hasOwnProperty(prop) && prop != "viewModelReference" && parentNode[prop] && typeof parentNode[prop] === 'object') {
                    drillDown(parentNode[prop], viewParentNode[prop]);
                }
            }
        };
        drillDown(actualModelParent, viewParentNode);
    };
    DataStoreManager.commitToActualModel = function (viewModelParent) {
        if (viewModelParent === void 0) { viewModelParent = this.CurrentViewModel; }
        var success;
        if (success) {
            // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
            this.ActualModel = _.cloneDeep(viewModelParent);
        }
        else {
            console.log("wtf");
        }
        return success;
    };
    DataStoreManager.setModelProp = function (propParent, propName, value, optionalCallBack) {
        propParent[propName] = value;
        if (optionalCallBack) {
            optionalCallBack();
        }
    };
    DataStoreManager.findById = function (obj, id) {
        //Early return
        if (obj.id === id) {
            return obj;
        }
        var result;
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop) && typeof obj[prop] === 'object') {
                result = this.findById(obj[prop], id);
                if (result) {
                    return result;
                }
            }
        }
        return result;
    };
    DataStoreManager.findByPropValue = function (obj, propName, value) {
        //Early return
        if (obj[propName] === value) {
            return obj;
        }
        var result;
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop) && typeof obj[prop] === 'object') {
                result = this.findByPropValue(obj[prop], propName, value);
                if (result) {
                    return result;
                }
            }
        }
        return result;
    };
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------
    DataStoreManager.classPropName = "Class";
    return DataStoreManager;
}());
