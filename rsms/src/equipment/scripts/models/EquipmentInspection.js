var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var EquipmentInspection = (function (_super) {
        __extends(EquipmentInspection, _super);
        function EquipmentInspection() {
            return _super.call(this) || this;
        }
        EquipmentInspection.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return EquipmentInspection;
    }(FluxCompositerBase));
    EquipmentInspection.urlMapping = new UrlMapping("getAllEquipmentInspections", "getEquipmentInspectionById&id=", "saveEquipmentInspection");
    EquipmentInspection.RoomMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Room", "getRoomById&id=", "Room", "Room_id");
    EquipmentInspection.PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=principalInvestigators&id={{UID}}", "PrincipalInvestigators", "Inspection_id", "Principal_investigator_id", "PrincipalInvestigatorEquipmentInspection", "getRelationships&class1=EquipmentInspection&class2=PrincipalInvestigator");
    equipment.EquipmentInspection = EquipmentInspection;
})(equipment || (equipment = {}));
