var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var BioSafetyCabinet = (function (_super) {
        __extends(BioSafetyCabinet, _super);
        function BioSafetyCabinet() {
            return _super.call(this) || this;
        }
        BioSafetyCabinet.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return BioSafetyCabinet;
    }(FluxCompositerBase));
    BioSafetyCabinet.urlMapping = new UrlMapping("getAllBioSafetyCabinets", "getBioSafetyCabinetById&id=", "saveBioSafetyCabinet");
    BioSafetyCabinet.EquipmentInspectionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "EquipmentInspection", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=EquipmentInspections&id={{UID}}", "EquipmentInspections", "Equipment_id");
    equipment.BioSafetyCabinet = BioSafetyCabinet;
})(equipment || (equipment = {}));
