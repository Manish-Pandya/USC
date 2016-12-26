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
            _super.call(this);
        }
        BioSafetyCabinet.prototype.onFulfill = function () {
            _super.prototype.onFulfill.call(this);
            this.getChildUrl(BioSafetyCabinet.EquipmentInspectionMap);
        };
        BioSafetyCabinet.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                //var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, true);
            }
            return this._hasGetAllPermission;
        };
        BioSafetyCabinet.urlMapping = new UrlMapping("getAllBioSafetyCabinets", "getBiosafetyCabinetById&id=", "saveBiosafetyCabinet");
        BioSafetyCabinet.EquipmentInspectionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "EquipmentInspection", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=EquipmentInspections&id={{UID}}", "EquipmentInspections", "Equipment_id");
        return BioSafetyCabinet;
    }(FluxCompositerBase));
    equipment.BioSafetyCabinet = BioSafetyCabinet;
})(equipment || (equipment = {}));
