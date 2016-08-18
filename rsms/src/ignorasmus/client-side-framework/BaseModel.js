var BaseModel = (function () {
    function BaseModel() {
    }
    BaseModel.prototype.getUrlMappings = function () { return this.urlMappings; };
    BaseModel.prototype.setUrlMappings = function (mappings) { this.urlMappings = mappings; };
    BaseModel.prototype.contruct = function () {
        if (!this.urlMappings) {
            console.log(new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server"));
        }
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.ClassPropName = this[DataStoreManager.classPropName];
        }
    };
    return BaseModel;
}());
