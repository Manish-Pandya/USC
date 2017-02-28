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
    var IBCQuestion = (function (_super) {
        __extends(IBCQuestion, _super);
        function IBCQuestion() {
            return _super.call(this) || this;
        }
        IBCQuestion.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCQuestion;
    }(FluxCompositerBase));
    IBCQuestion.urlMapping = new UrlMapping("getAllIBCQuestions", "getIBCQuestionById&id=", "saveIBCQuestion");
    IBCQuestion.PossibleAnswerMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCPossibleAnswer", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCPossibleAnswers&id={{UID}}", "IBCPossibleAnswers", "Question_id");
    ibc.IBCQuestion = IBCQuestion;
})(ibc || (ibc = {}));
