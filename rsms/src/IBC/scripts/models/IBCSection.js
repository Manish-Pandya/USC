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
    var IBCSection = /** @class */ (function (_super) {
        __extends(IBCSection, _super);
        function IBCSection() {
            return _super.call(this) || this;
        }
        IBCSection.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        IBCSection.urlMapping = new UrlMapping("getAllIBCSections", "getIBCSectionById&id=", "saveIBCSection");
        IBCSection.QuestionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCQuestion", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCQuestions&id={{UID}}", "IBCQuestions", "Section_id");
        return IBCSection;
    }(FluxCompositerBase));
    ibc.IBCSection = IBCSection;
})(ibc || (ibc = {}));
