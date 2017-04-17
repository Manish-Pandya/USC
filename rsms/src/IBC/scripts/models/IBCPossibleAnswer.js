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
    var IBCPossibleAnswer = (function (_super) {
        __extends(IBCPossibleAnswer, _super);
        function IBCPossibleAnswer() {
            return _super.call(this) || this;
        }
        IBCPossibleAnswer.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCPossibleAnswer;
    }(FluxCompositerBase));
    IBCPossibleAnswer.urlMapping = new UrlMapping("getAllIBCPossibleAnswers", "getIBCPossibleAnswerById&id=", "saveIBCPossibleAnswer");
    ibc.IBCPossibleAnswer = IBCPossibleAnswer;
})(ibc || (ibc = {}));
