var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var PrincipalInvestigator = (function (_super) {
    __extends(PrincipalInvestigator, _super);
    function PrincipalInvestigator() {
        _super.apply(this, arguments);
        this.UserMap = new CompositionMapping("ONE_TO_ONE", "User", "getUserById&id=" + this.UID, "User", "User_id");
        this.LabPersonnelMap = new CompositionMapping("ONE_TO_MANY", "User", "getUserById&id=" + this.UID, "LabPersonnel", "Supervisor_id");
    }
    PrincipalInvestigator.prototype.loadUser = function () {
        return this.User;
    };
    PrincipalInvestigator.prototype.loadLabPersonnel = function () {
        return this.labPersonnel;
    };
    PrincipalInvestigator.prototype.setUrlMappings = function () {
        var mappings = new UrlMapping("getAllPis", "getPiById&id=", "savePI");
        _super.prototype.setUrlMappings.call(this, mappings);
    };
    PrincipalInvestigator.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
    return PrincipalInvestigator;
}(BaseModel));
