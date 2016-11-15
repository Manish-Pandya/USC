var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var IBCProtocol = (function (_super) {
    __extends(IBCProtocol, _super);
    function IBCProtocol() {
        _super.call(this);
    }
    IBCProtocol.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    };
    IBCProtocol.urlMapping = new UrlMapping("getAllProtocols", "getProtocolById&id=", "saveProtocol");
    return IBCProtocol;
}(FluxCompositerBase));
