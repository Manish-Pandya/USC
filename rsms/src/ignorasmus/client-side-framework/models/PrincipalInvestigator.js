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
    PrincipalInvestigator.prototype.loadUser = function () {
        return this.User;
    };
    PrincipalInvestigator.prototype.loadLabPersonnel = function () {
        return this.labPersonnel;
    };
    PrincipalInvestigator.prototype.onFulfill = function (callback) {
        if (callback === void 0) { callback = null; }
        this.UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=" + this.UID, "User", "User_id");
        this.LabPersonnelMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getAllUsers", "LabPersonnel", "Supervisor_id");
        this.RoomMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getAllRooms", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=" + this.TypeName + "&class2=Room");
        return _super.prototype.onFulfill.call(this, callback);
    };
    PrincipalInvestigator.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
    PrincipalInvestigator.urlMapping = new UrlMapping("getAllPIs", "getPiById&id=", "savePI");
    return PrincipalInvestigator;
}(FluxCompositerBase));
