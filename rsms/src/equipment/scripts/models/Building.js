var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var Building = (function (_super) {
        __extends(Building, _super);
        function Building() {
            _super.call(this);
        }
        Building.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        Building.urlMapping = new UrlMapping("getAllBuildings", "getBuildingById&id=", "saveBuilding");
        return Building;
    }(FluxCompositerBase));
    equipment.Building = Building;
})(equipment || (equipment = {}));
