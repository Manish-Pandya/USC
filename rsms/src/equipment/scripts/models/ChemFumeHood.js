var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var equipment;
(function (equipment) {
    var ChemFumeHood = (function (_super) {
        __extends(ChemFumeHood, _super);
        function ChemFumeHood() {
            return _super.call(this) || this;
        }
        ChemFumeHood.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return ChemFumeHood;
    }(FluxCompositerBase));
    ChemFumeHood.urlMapping = new UrlMapping("getAllChemFumeHoods", "getChemFumeHoodById&id=", "saveChemFumeHood");
    ChemFumeHood.EquipmentInspectionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "EquipmentInspection", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=EquipmentInspections&id={{UID}}", "EquipmentInspections", "Equipment_id");
    equipment.ChemFumeHood = ChemFumeHood;
})(equipment || (equipment = {}));
