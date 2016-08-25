var BaseModel = (function () {
    function BaseModel() {
    }
    BaseModel.prototype.contruct = function () {
        if (!BaseModel.urlMapping) {
            console.log(new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server"));
        }
    };
    BaseModel.prototype.onFulfill = function () {
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }
    };
    BaseModel.urlMapping = new UrlMapping("foot", "", "");
    return BaseModel;
}());
