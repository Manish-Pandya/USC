var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var Laser = (function (_super) {
        __extends(Laser, _super);
        function Laser() {
            return _super.call(this) || this;
        }
        Laser.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return Laser;
    }(FluxCompositerBase));
    Laser.urlMapping = new UrlMapping("getAllLasers", "getLaserById&id=", "saveLaser");
    equipment.Laser = Laser;
})(equipment || (equipment = {}));
