var FluxCompositerBase = (function () {
    function FluxCompositerBase() {
    }
    FluxCompositerBase.prototype.contruct = function () {
        if (!FluxCompositerBase.urlMapping) {
            console.log(new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server"));
        }
    };
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
        var thisClass = this["constructor"];
        for (var instanceProp in thisClass) {
            if (thisClass[instanceProp] instanceof CompositionMapping) {
                allCompMaps.push(thisClass[instanceProp]);
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
    FluxCompositerBase.urlMapping = new UrlMapping("foot", "", "");
    return FluxCompositerBase;
}());
