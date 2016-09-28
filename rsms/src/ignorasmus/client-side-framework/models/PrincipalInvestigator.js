var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var PrincipalInvestigator = (function (_super) {
    __extends(PrincipalInvestigator, _super);
    function PrincipalInvestigator() {
        _super.call(this);
    }
    PrincipalInvestigator.prototype.onFulfill = function (callback) {
        if (callback === void 0) { callback = null; }
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        return _super.prototype.onFulfill.apply(this, [callback].concat(args));
        this.hasGetAllPermission();
    };
    PrincipalInvestigator.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.SAFETY_INSPECTOR];
            var len = currentRoles[1].length;
            for (var i = 0; i < len; i++) {
                var role = currentRoles[1][i];
            }
            _super.prototype.hasGetAllPermission.call(this);
        }
        return this._hasGetAllPermission;
    };
    PrincipalInvestigator.urlMapping = new UrlMapping("getAllPIs", "getPIById&id=", "savePI");
    PrincipalInvestigator.UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "User_id");
    PrincipalInvestigator.LabPersonnelMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getAllUsers", "LabPersonnel", "Supervisor_id");
    PrincipalInvestigator.RoomMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getAllRooms", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");
    return PrincipalInvestigator;
}(FluxCompositerBase));
