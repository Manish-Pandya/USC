var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
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
    IBCPossibleAnswer.ResponseMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=Responses&id={{UID}}", "IBCResponses", "Answer_id");
    ibc.IBCPossibleAnswer = IBCPossibleAnswer;
})(ibc || (ibc = {}));
