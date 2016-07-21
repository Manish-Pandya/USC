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
            DataStoreManager._actualModel = value;
            // TODO: Conditionally Deepcopy relevant CurrentViewModel and/or CurrentModalViewModel properties from the ActualModel.
            this.CurrentViewModel = _.cloneDeep(value);
            this.CurrentModalViewModel = _.cloneDeep(value);
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
                viewModelParent[type] = viewModelParent[type] || this.getAll(type, viewModelParent);
                return viewModelParent[type];
        }
    };
    DataStoreManager.commitToActualModel = function (viewModelParent) {
        if (viewModelParent === void 0) { viewModelParent = this.CurrentViewModel; }
        var success;
        if (success) {
            // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
            this.ActualModel = viewModelParent;
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
    DataStoreManager.Howdy = "Hello World";
    return DataStoreManager;
}());
