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
var ibc;
(function (ibc) {
    var PrincipalInvestigator = (function (_super) {
        __extends(PrincipalInvestigator, _super);
        function PrincipalInvestigator() {
            return _super.call(this) || this;
        }
        PrincipalInvestigator.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return PrincipalInvestigator;
    }(FluxCompositerBase));
    PrincipalInvestigator.urlMapping = new UrlMapping("getAllIBCPIs", "getPIById&id=", "savePI");
    PrincipalInvestigator.UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "User_id");
    PrincipalInvestigator.LabPersonnelMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=LabPersonnel&id={{UID}}", "LabPersonnel", "Supervisor_id");
    PrincipalInvestigator.RoomMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=rooms&id={{UID}}", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");
    ibc.PrincipalInvestigator = PrincipalInvestigator;
})(ibc || (ibc = {}));
