var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ibc;
(function (ibc) {
    var Hazard = (function (_super) {
        __extends(Hazard, _super);
        function Hazard() {
            return _super.call(this) || this;
        }
        Hazard.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return Hazard;
    }(FluxCompositerBase));
    Hazard.urlMapping = new UrlMapping("getAllHazards", "getHazardById&id=", "saveHazard");
    ibc.Hazard = Hazard;
})(ibc || (ibc = {}));