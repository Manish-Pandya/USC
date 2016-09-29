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
    Role.prototype.onFulfill = function (callback) {
        if (callback === void 0) { callback = null; }
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        this.hasGetAllPermission();
        // build compositionMapping
        return _super.prototype.onFulfill.apply(this, [callback].concat(args));
    };
    Role.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            _super.prototype.hasGetAllPermission.call(this, _.intersection(currentRoles, allowedRoles).length);
        }
        return this._hasGetAllPermission;
    };
    Role.urlMapping = new UrlMapping("getAllRoles", "getRoleById&id=", "saveRole");
    return Role;
}(FluxCompositerBase));
