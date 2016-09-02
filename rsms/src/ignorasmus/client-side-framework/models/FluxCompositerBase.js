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
    FluxCompositerBase.prototype.doCompose = function (compMap) {
        if (compMap === void 0) { compMap = null; }
        if (compMap) {
        }
        else {
        }
    };
    FluxCompositerBase.urlMapping = new UrlMapping("foot", "", "");
    return FluxCompositerBase;
}());
