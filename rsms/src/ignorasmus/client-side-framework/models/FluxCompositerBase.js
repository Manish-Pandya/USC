////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var CompositionMapping = (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    /**
     * Models the relationship between classes, providing URIs to fetch child objects
     *
     * Instances of this utility class should be contructed by your classes in onFullfill or later
     *
     * @param compositionType
     * @param childType
     * @param childUrl
     * @ex "getPropertyByName&type=" + this[DataStoreManager.classPropName] + "&property=rooms&id=" + this.UID
     * @param propertyName
     * @param childIdProp
     * @param parentIdProp
     * @param gerundName
     * @param gerundUrl
     */
    function CompositionMapping(compositionType, childType, childUrl, propertyName, childIdProp, parentIdProp, gerundName, gerundUrl) {
        this.CompositionType = compositionType;
        this.ChildType = childType;
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        this.ChildIdProp = childIdProp;
        this.ParentIdProp = parentIdProp || DataStoreManager.uidString;
        if (this.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this MANY TO MANY compositional relationship");
            }
            else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
    }
    return CompositionMapping;
}());
//----------------------------------------------------------------------
//
//  Properties
//
//----------------------------------------------------------------------
CompositionMapping.ONE_TO_ONE = "ONE_TO_ONE";
CompositionMapping.ONE_TO_MANY = "ONE_TO_MANY";
CompositionMapping.MANY_TO_MANY = "MANY_TO_MANY";
var FluxCompositerBase = (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    /**
     *
     */
    function FluxCompositerBase() {
        this.viewModelWatcher = null;
        this._hasGetAllPermission = null;
        if (!FluxCompositerBase.urlMapping) {
            console.log(new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server"));
        }
        this.thisClass = this.constructor;
    }
    Object.defineProperty(FluxCompositerBase.prototype, "allCompMaps", {
        get: function () {
            if (!this._allCompMaps) {
                this._allCompMaps = [];
                for (var instanceProp in this.thisClass) {
                    if (this.thisClass[instanceProp] instanceof CompositionMapping) {
                        var cm = this.thisClass[instanceProp];
                        if (cm.ChildUrl == InstanceFactory._nameSpace[cm.ChildType].urlMapping.urlGetAll) {
                            cm.callGetAll = true; // flag that getAll will be called
                        }
                        this._allCompMaps.push(cm);
                    }
                }
            }
            return this._allCompMaps;
        },
        enumerable: true,
        configurable: true
    });
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    /**
     * Returns the relevant CompositionMapping for a given property.
     *
     * @param property
     */
    FluxCompositerBase.prototype.getCompMapFromProperty = function (property) {
        var cms = this.allCompMaps;
        var l = cms.length;
        for (var i = 0; i < l; i++) {
            var cm = cms[i];
            if (cm.PropertyName == property)
                return cm;
        }
        return;
    };
    /**
     * Fires once instance retrieves data and fullfills dependencies.
     * Handy for overriding in child classes.
     *
     */
    FluxCompositerBase.prototype.onFulfill = function () {
        this.hasGetAllPermission();
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }
    };
    /**
     * Builds shild instances for all or a given array of CompositionMappings.
     *
     * @param compMaps
     */
    FluxCompositerBase.prototype.doCompose = function (compMaps) {
        if (!compMaps)
            return this;
        if (Array.isArray(compMaps)) {
            // compose just properties in array...
            var len = compMaps.length;
            for (var i = 0; i < len; i++) {
                if (_.findIndex(this.allCompMaps, compMaps[i]) > -1) {
                    InstanceFactory.getChildInstances(compMaps[i], this);
                }
            }
        }
        else {
            // compose all compmaps...
            var len = this.allCompMaps.length;
            for (var i = 0; i < len; i++) {
                InstanceFactory.getChildInstances(this.allCompMaps[i], this);
            }
        }
        return this;
    };
    /**
     * Determines if class has required permissions. Intended for overriding.
     *
     * @param evaluator
     */
    FluxCompositerBase.prototype.hasGetAllPermission = function (evaluator) {
        if (evaluator === void 0) { evaluator = false; }
        if (this._hasGetAllPermission == null) {
            this._hasGetAllPermission = (typeof evaluator == "function") ? evaluator() : evaluator;
        }
        return this._hasGetAllPermission;
    };
    /**
     * Dynamically constructs a class' CompositionMapping instance's childUrl based on this instance's properties.
     *
     * @param cm
     */
    FluxCompositerBase.prototype.getChildUrl = function (cm) {
        var _this = this;
        var pattern = /\{\{\s*([a-zA-Z_\-&\$\[\]][a-zA-Z0-9_\-&\$\[\]\.]*)\s*\}\}/g;
        var str = cm.ChildUrl.replace(pattern, function (sub) {
            sub = sub.match(/\{\{(.*)\}\}/)[1];
            var parts = sub.split(".");
            sub = parts[0];
            for (var n = 1; n < parts.length; n++) {
                sub += "['" + parts[n] + "']";
            }
            if (parts.length > 1) {
                sub = eval(sub);
            }
            return _this[sub];
        });
        return str;
    };
    return FluxCompositerBase;
}());
//----------------------------------------------------------------------
//
//  Properties
//
//----------------------------------------------------------------------
FluxCompositerBase.urlMapping = new UrlMapping("test", "", "");
