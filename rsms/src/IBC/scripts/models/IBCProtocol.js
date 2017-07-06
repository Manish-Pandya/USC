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
    var IBCProtocol = (function (_super) {
        __extends(IBCProtocol, _super);
        function IBCProtocol() {
            var _this = _super.call(this) || this;
            _this.PrincipalInvestigators = [];
            return _this;
        }
        IBCProtocol.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        IBCProtocol.urlMapping = new UrlMapping("getAllProtocols", "getProtocolById&id=", "saveProtocol");
        IBCProtocol.HazardMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Hazard", "getHazardById&id={{this.Hazard_id}}", "Hazard", "Hazard_id");
        IBCProtocol.DepartmentMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Department", "getDepartmentById&id={{this.Department_id}}", "Department", "Department_id");
        IBCProtocol.PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=PrincipalInvestigator&id={{UID}}", "PrincipalInvestigators", "Protocol_id", "Principal_investigator_id", "IBCProtocolPrincipalInvestigator", "getRelationships&class1=IBCProtocol&class2=PrincipalInvestigator");
        IBCProtocol.RevisionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocolRevision", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCProtocolRevisions&id={{UID}}", "IBCProtocolRevisions", "Protocol_id");
        IBCProtocol.SectionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCSection", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCSections&id={{UID}}", "IBCSections", "Hazard_id", "Hazard_id");
        return IBCProtocol;
    }(FluxCompositerBase));
    ibc.IBCProtocol = IBCProtocol;
})(ibc || (ibc = {}));
