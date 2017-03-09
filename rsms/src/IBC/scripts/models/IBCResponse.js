var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ibc;
(function (ibc) {
    var IBCResponse = (function (_super) {
        __extends(IBCResponse, _super);
        function IBCResponse() {
            return _super.call(this) || this;
        }
        IBCResponse.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.IBC_CHAIR];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCResponse;
    }(FluxCompositerBase));
    IBCResponse.urlMapping = new UrlMapping("getAllIBCResponses", "getIBCResponseById&id=", "saveIBCResponse");
    ibc.IBCResponse = IBCResponse;
})(ibc || (ibc = {}));
