var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var IBCSection = (function (_super) {
    __extends(IBCSection, _super);
    function IBCSection() {
        return _super.call(this) || this;
    }
    IBCSection.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    };
    return IBCSection;
}(FluxCompositerBase));
IBCSection.urlMapping = new UrlMapping("getAllSections", "getSectionById&id=", "saveSection");
