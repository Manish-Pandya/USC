var CompositionMapping = (function () {
    /**
     *
     * Models the relationship between classes, providing URIs to fetch child objects
     *
     * Instances of this utility class should be contructed by your classes in onFullfill or later
     *
     * @param compositionType
     * @param childType
     * @param childUrl
     *@ex "getPropertyByName&type=" + this[DataStoreManager.classPropName] + "&property=rooms&id=" + this.UID
     * @param propertyName
     * @param childIdProp
     * @param parentIdProp
     * @param gerundName
     * @param gerundUrl
     */
    function CompositionMapping(compositionType, childType, childUrl, propertyName, childIdProp, parentIdProp, gerundName, gerundUrl) {
        if (parentIdProp === void 0) { parentIdProp = null; }
        if (gerundName === void 0) { gerundName = null; }
        if (gerundUrl === void 0) { gerundUrl = null; }
        //temp values for erasmus.  add to global config as optional param
        this.DEFAULT_MANY_TO_MANY_PARENT_ID = "ParentId";
        this.DEFAULT_MANY_TO_MANY_CHILD_ID = "ChildId";
        if (!window[childType]) {
            throw new Error("what the fuck brogrammer?  you contructed this shit too soon.");
        }
        this.CompositionType = compositionType;
        this.ChildType = childType;
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        this.ChildIdProp = childIdProp;
        if (parentIdProp) {
            this.ParentIdProp = parentIdProp;
        }
        else {
            this.ParentIdProp = DataStoreManager.uidString;
        }
        if (this.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this MANY TO MANY compositional relationship");
            }
            else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
        if (this.ChildUrl == window[this.ChildType].urlMapping.urlGetAll) {
            // flag that getAll will be called
            this.callGetAll = true;
        }
    }
    CompositionMapping.ONE_TO_ONE = "ONE_TO_ONE";
    CompositionMapping.ONE_TO_MANY = "ONE_TO_MANY";
    CompositionMapping.MANY_TO_MANY = "MANY_TO_MANY";
    return CompositionMapping;
}());
var FluxCompositerBase = (function () {
    function FluxCompositerBase() {
        this._hasGetAllPermission = null;
        if (!FluxCompositerBase.urlMapping) {
            console.log(new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server"));
        }
        this.thisClass = this.constructor;
    }
    FluxCompositerBase.prototype.onFulfill = function (callback) {
        if (callback === void 0) { callback = null; }
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }
        return callback ? callback(args) : null;
    };
    FluxCompositerBase.prototype.doCompose = function (compMaps) {
        var allCompMaps = [];
        for (var instanceProp in this) {
            if (this[instanceProp] instanceof CompositionMapping) {
                allCompMaps.push(this[instanceProp]);
            }
        }
        //console.log(allCompMaps);
        if (compMaps) {
            if (Array.isArray(compMaps)) {
                // compose just properties in array...
                var len = compMaps.length;
                for (var i = 0; i < len; i++) {
                    if (allCompMaps.indexOf(compMaps[i]) > -1) {
                        InstanceFactory.getChildInstances(compMaps[i], this);
                    }
                    else {
                        console.log(new Error("compMap not found for property " + this.TypeName + "." + compMaps[i].PropertyName));
                    }
                }
            }
            else {
                // compose all compmaps...
                var len = allCompMaps.length;
                for (var i = 0; i < len; i++) {
                    InstanceFactory.getChildInstances(allCompMaps[i], this);
                }
            }
        }
    };
    FluxCompositerBase.prototype.hasGetAllPermission = function (evaluator) {
        if (evaluator === void 0) { evaluator = false; }
        if (this._hasGetAllPermission == null) {
            if (typeof evaluator == "function") {
                this._hasGetAllPermission = evaluator();
            }
            else {
                this._hasGetAllPermission = evaluator;
            }
        }
        console.log(this.thisClass.name + " has getAll permission:", this._hasGetAllPermission);
        return this._hasGetAllPermission;
    };
    FluxCompositerBase.urlMapping = new UrlMapping("foot", "", "");
    return FluxCompositerBase;
}());
