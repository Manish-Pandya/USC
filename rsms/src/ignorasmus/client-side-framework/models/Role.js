var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var Role = (function (_super) {
    __extends(Role, _super);
    function Role() {
        _super.call(this);
    }
    Role.prototype.onFulfill = function () {
        this.hasGetAllPermission();
        _super.prototype.onFulfill.call(this);
        // build compositionMapping
    };
    Role.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            _super.prototype.hasGetAllPermission.call(this, _.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    };
    Role.urlMapping = new UrlMapping("getAllRoles", "getRoleById&id=", "saveRole");
    return Role;
}(FluxCompositerBase));
