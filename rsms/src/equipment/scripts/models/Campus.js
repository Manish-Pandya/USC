var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var Campus = (function (_super) {
        __extends(Campus, _super);
        function Campus() {
            return _super.call(this) || this;
        }
        Campus.prototype.onFulfill = function () {
            _super.prototype.onFulfill.call(this);
            this.getChildUrl(equipment.BioSafetyCabinet.EquipmentInspectionMap);
        };
        Campus.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return Campus;
    }(FluxCompositerBase));
    Campus.urlMapping = new UrlMapping("getAllCampuses", "getCampusById&id=", "");
    equipment.Campus = Campus;
})(equipment || (equipment = {}));
