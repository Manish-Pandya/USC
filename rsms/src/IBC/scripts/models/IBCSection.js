var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ibc;
(function (ibc) {
    var IBCSection = (function (_super) {
        __extends(IBCSection, _super);
        function IBCSection() {
            return _super.call(this) || this;
        }
        IBCSection.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.IBC_CHAIR];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCSection;
    }(FluxCompositerBase));
    IBCSection.urlMapping = new UrlMapping("getAllIBCSections", "getIBCSectionById&id=", "saveIBCSection");
    IBCSection.QuestionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCQuestion", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCQuestions&id={{UID}}", "IBCQuestions", "Section_id");
    ibc.IBCSection = IBCSection;
})(ibc || (ibc = {}));
