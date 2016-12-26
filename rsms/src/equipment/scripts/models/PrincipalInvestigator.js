var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var PrincipalInvestigator = (function (_super) {
        __extends(PrincipalInvestigator, _super);
        function PrincipalInvestigator() {
            return _super.call(this) || this;
        }
        PrincipalInvestigator.prototype.onFulfill = function () {
            _super.prototype.onFulfill.call(this);
        };
        PrincipalInvestigator.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return PrincipalInvestigator;
    }(FluxCompositerBase));
    PrincipalInvestigator.urlMapping = new UrlMapping("getAllPIs", "getPIById&id=", "savePI");
    PrincipalInvestigator.RoomMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=rooms&id={{UID}}", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");
    equipment.PrincipalInvestigator = PrincipalInvestigator;
})(equipment || (equipment = {}));
